@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <h2>Daftar Dokumen</h2>
        <a href="{{ route('documents.create') }}" class="btn btn-primary mb-3">📤 Upload Dokumen Baru</a>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="5%">No</th>
                        <th width="25%">Judul</th>
                        <th width="20%">Nama File</th>
                        <th width="10%">Tipe</th>
                        <th width="15%">Dibuat</th>
                        <th width="25%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($documents as $index => $document)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $document->title }}</strong>
                            <br>
                            <small class="text-muted">ID: {{ $document->document_key }}</small>
                        </td>
                        <td>
                            {{ $document->filename }}
                            <br>
                            <small class="text-muted">
                                {{ number_format(Storage::disk('public')->size($document->file_path) / 1024, 2) }} KB
                            </small>
                        </td>
                        <td class="text-center">
                            @php
                                $typeColors = [
                                    'docx' => 'primary',
                                    'doc' => 'primary',
                                    'xlsx' => 'success',
                                    'xls' => 'success',
                                    'pptx' => 'danger',
                                    'ppt' => 'danger',
                                ];
                                $color = $typeColors[$document->file_type] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $color }}">
                                {{ strtoupper($document->file_type) }}
                            </span>
                        </td>
                        <td>
                            {{ $document->created_at->format('d/m/Y') }}
                            <br>
                            <small class="text-muted">{{ $document->created_at->format('H:i') }}</small>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <!-- Preview Button -->
                                <button type="button" class="btn btn-sm btn-info dropdown-toggle" data-bs-toggle="dropdown">
                                    👁️ Preview
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('documents.preview', $document->id) }}">
                                            📄 Full Preview (OnlyOffice)
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="{{ route('documents.quick-preview', $document->id) }}">
                                            ⚡ Quick Preview
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item" href="#" onclick="shareDoc({{ $document->id }})">
                                            🔗 Get Share Link
                                        </a>
                                    </li>
                                </ul>

                                <!-- Edit Button -->
                                <a href="{{ route('documents.edit', $document->id) }}" class="btn btn-sm btn-warning">
                                    ✏️ Edit
                                </a>

                                <!-- Download Button -->
                                <a href="{{ route('documents.download', $document->id) }}" class="btn btn-sm btn-success">
                                    ⬇️ Download
                                </a>

                                <!-- Delete Button -->
                                <form action="{{ route('documents.destroy', $document->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Yakin hapus dokumen ini?')">
                                        🗑️ Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4">
                            <div class="text-muted">
                                <h5>📭 Belum ada dokumen</h5>
                                <p>Klik tombol "Upload Dokumen Baru" untuk menambahkan dokumen pertama Anda.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($documents->count() > 0)
        <div class="card mt-3">
            <div class="card-body">
                <h5 class="card-title">📊 Statistik</h5>
                <div class="row text-center">
                    <div class="col-md-3">
                        <h4>{{ $documents->count() }}</h4>
                        <p class="text-muted">Total Dokumen</p>
                    </div>
                    <div class="col-md-3">
                        <h4>{{ $documents->where('file_type', 'docx')->count() + $documents->where('file_type', 'doc')->count() }}</h4>
                        <p class="text-muted">Word Documents</p>
                    </div>
                    <div class="col-md-3">
                        <h4>{{ $documents->where('file_type', 'xlsx')->count() + $documents->where('file_type', 'xls')->count() }}</h4>
                        <p class="text-muted">Excel Sheets</p>
                    </div>
                    <div class="col-md-3">
                        <h4>{{ $documents->where('file_type', 'pptx')->count() + $documents->where('file_type', 'ppt')->count() }}</h4>
                        <p class="text-muted">PowerPoint</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function shareDoc(id) {
    fetch('/documents/' + id + '/share')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Create modal or alert with share link
                var shareModal = `
                    <div class="modal fade" id="shareModal" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">🔗 Share Link</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p>Share link (valid for ${data.expiresIn}):</p>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="shareUrl" value="${data.shareUrl}" readonly>
                                        <button class="btn btn-primary" onclick="copyShareUrl()">📋 Copy</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                document.body.insertAdjacentHTML('beforeend', shareModal);
                var modal = new bootstrap.Modal(document.getElementById('shareModal'));
                modal.show();

                // Remove modal after close
                document.getElementById('shareModal').addEventListener('hidden.bs.modal', function() {
                    this.remove();
                });
            }
        });
}

function copyShareUrl() {
    var copyText = document.getElementById("shareUrl");
    copyText.select();
    document.execCommand("copy");

    // Show success message
    var btn = event.target;
    var originalText = btn.innerHTML;
    btn.innerHTML = '✅ Copied!';
    btn.classList.remove('btn-primary');
    btn.classList.add('btn-success');

    setTimeout(function() {
        btn.innerHTML = originalText;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-primary');
    }, 2000);
}
</script>
@endsection
