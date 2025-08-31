<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class DocumentController extends Controller
{
    private $onlyofficeUrl;
    private $jwtSecret;

    public function __construct()
    {
        // $this->onlyofficeUrl = env('ONLYOFFICE_SERVER_URL');
        // $this->jwtSecret = env('ONLYOFFICE_JWT_SECRET');

        $this->onlyofficeUrl = config('onlyoffice.server_url');
        $this->jwtSecret = config('onlyoffice.jwt_secret');

        // Debug untuk melihat nilai yang dibaca
        \Log::info('OnlyOffice Config Debug', [
            'server_url' => $this->onlyofficeUrl,
            'jwt_secret_length' => strlen($this->jwtSecret ?? ''),
            'jwt_secret_empty' => empty($this->jwtSecret),
            'env_direct' => env('ONLYOFFICE_JWT_SECRET')
        ]);

        // Tambahkan validasi
        if (empty($this->jwtSecret)) {
            throw new \Exception('ONLYOFFICE_JWT_SECRET not configured in .env file');
        }
    }

    // Halaman utama - daftar dokumen
    public function index()
    {
        $documents = Document::all();
        return view('documents.index', compact('documents'));
    }

    // Form upload dokumen baru
    public function create()
    {
        return view('documents.create');
    }

    // Simpan dokumen yang diupload
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'file' => 'required|mimes:doc,docx,xlsx,xls,pptx,ppt|max:10240'
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('documents', $filename, 'public');

            $document = Document::create([
                'title' => $request->title,
                'filename' => $filename,
                'file_path' => $path,
                'file_type' => $file->getClientOriginalExtension(),
                'document_key' => uniqid('doc_')
            ]);

            return redirect()->route('documents.index')
                ->with('success', 'Dokumen berhasil diupload');
        }

        return back()->with('error', 'Gagal upload dokumen');
    }

    // Buka editor OnlyOffice
    public function edit($id)
    {
        $document = Document::findOrFail($id);

        // Konfigurasi untuk OnlyOffice
        $config = [
            'document' => [
                'fileType' => $document->file_type,
                'key' => $document->document_key . '_' . $document->updated_at->timestamp,
                'title' => $document->title,
                'url' => url('documents/download/' . $document->id)
            ],
            'documentType' => $this->getDocumentType($document->file_type),
            'editorConfig' => [
                'callbackUrl' => url('documents/callback/' . $document->id),
                'mode' => 'edit',
                'lang' => 'id',
                'user' => [
                    'id' => '1',
                    'name' => 'User Demo'
                ]
            ]
        ];

        // Generate JWT token
        $token = JWT::encode($config, $this->jwtSecret, 'HS256');

        return view('documents.edit', [
            'document' => $document,
            'config' => json_encode($config),
            'token' => $token,
            'onlyofficeUrl' => $this->onlyofficeUrl
        ]);
    }

    // Download dokumen untuk OnlyOffice
    public function download($id)
    {
        $document = Document::findOrFail($id);
        $path = storage_path('app/public/' . $document->file_path);

        if (file_exists($path)) {
            return response()->download($path, $document->filename);
        }

        abort(404);
    }

    // Callback dari OnlyOffice untuk menyimpan perubahan
    public function callback(Request $request, $id)
    {
        $document = Document::findOrFail($id);

        // Decode JWT jika ada
        $token = $request->header('Authorization');
        if ($token) {
            $token = substr($token, 7); // Remove 'Bearer '
            try {
                $payload = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
            } catch (\Exception $e) {
                return response()->json(['error' => 1, 'message' => 'Invalid token'], 403);
            }
        }

        $data = $request->all();

        // Status dari OnlyOffice
        // 0 - no document with the key identifier
        // 1 - document is being edited
        // 2 - document is ready for saving
        // 3 - document saving error
        // 4 - document is closed with no changes
        // 6 - document is being edited, but the current document state is saved
        // 7 - error has occurred while force saving the document

        $status = $data['status'] ?? 0;

        if ($status == 2 || $status == 6) {
            // Download file yang sudah diedit dari OnlyOffice
            $downloadUrl = $data['url'];
            $newContent = file_get_contents($downloadUrl);

            if ($newContent !== false) {
                // Simpan file yang sudah diedit
                $path = storage_path('app/public/' . $document->file_path);
                file_put_contents($path, $newContent);

                // Update timestamp untuk refresh document key
                $document->touch();

                return response()->json(['error' => 0]);
            }
        }

        return response()->json(['error' => 0]);
    }


// public function callback(Request $request, $id)
// {
//     // Logging untuk debug
//     \Log::info('OnlyOffice Callback Called', [
//         'document_id' => $id,
//         'headers' => $request->headers->all(),
//         'body' => $request->all(),
//         'method' => $request->method()
//     ]);

//     $document = Document::findOrFail($id);

//     // Skip JWT validation untuk testing dulu
//     // Nanti bisa diaktifkan lagi setelah berfungsi
//     /*
//     $token = $request->header('Authorization');
//     if ($token) {
//         $token = substr($token, 7);
//         try {
//             $payload = JWT::decode($token, new Key($this->jwtSecret, 'HS256'));
//         } catch (\Exception $e) {
//             \Log::error('JWT Validation Failed', ['error' => $e->getMessage()]);
//             return response()->json(['error' => 1, 'message' => 'Invalid token'], 403);
//         }
//     }
//     */

//     $data = $request->all();
//     $status = $data['status'] ?? 0;

//     \Log::info('OnlyOffice Status', ['status' => $status]);

//     // Status 1 = sedang diedit (normal, abaikan)
//     if ($status == 1) {
//         return response()->json(['error' => 0]);
//     }

//     // Status 2 = ready to save, Status 6 = force save
//     if ($status == 2 || $status == 6) {
//         try {
//             $downloadUrl = $data['url'];
//             \Log::info('Downloading from OnlyOffice', ['url' => $downloadUrl]);

//             // Download file dengan timeout yang lebih lama
//             $context = stream_context_create([
//                 'http' => [
//                     'timeout' => 60,
//                     'ignore_errors' => true
//                 ]
//             ]);

//             $newContent = file_get_contents($downloadUrl, false, $context);

//             if ($newContent !== false) {
//                 $path = storage_path('app/public/' . $document->file_path);
//                 file_put_contents($path, $newContent);
//                 $document->touch();

//                 \Log::info('Document saved successfully', ['path' => $path]);
//                 return response()->json(['error' => 0]);
//             } else {
//                 \Log::error('Failed to download document from OnlyOffice');
//                 return response()->json(['error' => 3]);
//             }
//         } catch (\Exception $e) {
//             \Log::error('Callback Exception', ['error' => $e->getMessage()]);
//             return response()->json(['error' => 3]);
//         }
//     }

//     // Status 4 = closed without changes
//     if ($status == 4) {
//         \Log::info('Document closed without changes');
//         return response()->json(['error' => 0]);
//     }

//     // Status 3 = save error, Status 7 = force save error
//     if ($status == 3 || $status == 7) {
//         \Log::error('OnlyOffice reported save error', ['status' => $status]);
//         return response()->json(['error' => 0]);
//     }

//     return response()->json(['error' => 0]);
// }

    // Helper untuk menentukan tipe dokumen
    private function getDocumentType($extension)
    {
        $wordExtensions = ['doc', 'docx', 'docm', 'dot', 'dotx', 'dotm', 'odt', 'rtf', 'txt'];
        $cellExtensions = ['xls', 'xlsx', 'xlsm', 'xlt', 'xltx', 'xltm', 'ods', 'csv'];
        $slideExtensions = ['pps', 'ppsx', 'ppsm', 'ppt', 'pptx', 'pptm', 'pot', 'potx', 'potm', 'odp'];

        if (in_array($extension, $wordExtensions)) {
            return 'word';
        } elseif (in_array($extension, $cellExtensions)) {
            return 'cell';
        } elseif (in_array($extension, $slideExtensions)) {
            return 'slide';
        }

        return 'word';
    }

    // Hapus dokumen
    public function destroy($id)
    {
        $document = Document::findOrFail($id);

        // Hapus file fisik
        Storage::disk('public')->delete($document->file_path);

        // Hapus dari database
        $document->delete();

        return redirect()->route('documents.index')
            ->with('success', 'Dokumen berhasil dihapus');
    }

    // Method baru untuk preview dokumen (view-only)
    public function preview($id)
    {
        $document = Document::findOrFail($id);

        // Simplified config untuk preview
        $config = [
            'document' => [
                'fileType' => $document->file_type,
                'key' => $document->document_key . '_' . time(), // Unique key
                'title' => $document->title,
                'url' => url('documents/download/' . $document->id)
            ],
            'documentType' => $this->getDocumentType($document->file_type),
            'editorConfig' => [
                'mode' => 'view', // View mode only
                'lang' => 'id'
            ],
            'type' => 'desktop', // Set type to desktop for better rendering
            'width' => '100%',
            'height' => '100%'
        ];

        // Generate JWT token
        $token = JWT::encode($config, $this->jwtSecret, 'HS256');

        // Log for debugging
        \Log::info('Preview Config', [
            'document_id' => $id,
            'config' => $config,
            'download_url' => url('documents/download/' . $document->id)
        ]);

        return view('documents.preview', [
            'document' => $document,
            'config' => json_encode($config, JSON_UNESCAPED_SLASHES),
            'token' => $token,
            'onlyofficeUrl' => $this->onlyofficeUrl
        ]);
    }

    // Method untuk preview dengan embedded viewer (alternatif - lebih ringan)
    public function quickPreview($id)
    {
        $document = Document::findOrFail($id);

        // Untuk file Word, Excel, PowerPoint bisa menggunakan Office Online Viewer
        $supportedTypes = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx'];

        if (in_array($document->file_type, $supportedTypes)) {
            $fileUrl = url('documents/download/' . $document->id);
            // URL encode untuk Office Online Viewer
            $viewerUrl = 'https://view.officeapps.live.com/op/embed.aspx?src=' . urlencode($fileUrl);

            return view('documents.quick-preview', [
                'document' => $document,
                'viewerUrl' => $viewerUrl,
                'fileUrl' => $fileUrl
            ]);
        }

        // Jika tidak support, redirect ke preview OnlyOffice
        return redirect()->route('documents.preview', $document->id);
    }

    // Method untuk print dokumen
    public function print($id)
    {
        $document = Document::findOrFail($id);

        // Konfigurasi khusus untuk print
        $config = [
            'document' => [
                'fileType' => $document->file_type,
                'key' => $document->document_key . '_print_' . time(),
                'title' => $document->title,
                'url' => url('documents/download/' . $document->id),
                'permissions' => [
                    'download' => false,
                    'edit' => false,
                    'print' => true,
                    'copy' => false
                ]
            ],
            'documentType' => $this->getDocumentType($document->file_type),
            'editorConfig' => [
                'mode' => 'view',
                'lang' => 'id',
                'customization' => [
                    'autosave' => false,
                    'compactHeader' => true,
                    'compactToolbar' => true,
                    'hideRightMenu' => true,
                    'toolbarNoTabs' => true
                ],
                'embedded' => [
                    'embedUrl' => url('documents/download/' . $document->id),
                    'fullscreenUrl' => url('documents/preview/' . $document->id),
                    'saveUrl' => '',
                    'shareUrl' => '',
                    'toolbarDocked' => 'top'
                ]
            ],
            'events' => [
                'onAppReady' => 'onAppReady',
                'onDocumentReady' => 'onDocumentReady'
            ],
            'type' => 'embedded'
        ];

        $token = JWT::encode($config, $this->jwtSecret, 'HS256');

        return view('documents.print', [
            'document' => $document,
            'config' => json_encode($config),
            'token' => $token,
            'onlyofficeUrl' => $this->onlyofficeUrl
        ]);
    }

    // Method untuk share dokumen (generate shareable link)
    public function share($id)
    {
        $document = Document::findOrFail($id);

        // Generate unique share token
        $shareToken = base64_encode($document->id . '|' . time());

        // Simpan token ke database jika perlu (optional)
        // Atau gunakan cache untuk temporary share
        \Cache::put('share_' . $shareToken, $document->id, now()->addDays(7));

        $shareUrl = url('documents/shared/' . $shareToken);

        return response()->json([
            'success' => true,
            'shareUrl' => $shareUrl,
            'expiresIn' => '7 days'
        ]);
    }

    // Method untuk akses shared document
    public function shared($token)
    {
        $documentId = \Cache::get('share_' . $token);

        if (!$documentId) {
            abort(404, 'Link sharing sudah expired atau tidak valid');
        }

        $document = Document::findOrFail($documentId);

        // Konfigurasi untuk shared view (read-only)
        $config = [
            'document' => [
                'fileType' => $document->file_type,
                'key' => $document->document_key . '_shared_' . md5($token),
                'title' => $document->title,
                'url' => url('documents/download/' . $document->id),
                'permissions' => [
                    'comment' => false,
                    'download' => true,
                    'edit' => false,
                    'print' => true,
                    'copy' => true
                ]
            ],
            'documentType' => $this->getDocumentType($document->file_type),
            'editorConfig' => [
                'mode' => 'view',
                'lang' => 'id',
                'user' => [
                    'id' => 'guest_' . md5($token),
                    'name' => 'Guest Viewer'
                ],
                'customization' => [
                    'logo' => [
                        'image' => '',
                        'imageEmbedded' => '',
                        'url' => url('/')
                    ],
                    'goback' => [
                        'text' => 'Kembali',
                        'url' => url('/')
                    ]
                ]
            ]
        ];

        $jwtToken = JWT::encode($config, $this->jwtSecret, 'HS256');

        return view('documents.shared', [
            'document' => $document,
            'config' => json_encode($config),
            'token' => $jwtToken,
            'onlyofficeUrl' => $this->onlyofficeUrl
        ]);
    }


}
