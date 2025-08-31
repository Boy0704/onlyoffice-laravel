@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <h2>Daftar Dokumen</h2>
        <a href="{{ route('documents.create') }}" class="btn btn-primary mb-3">Upload Dokumen Baru</a>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Judul</th>
                    <th>Nama File</th>
                    <th>Tipe</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documents as $index => $document)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $document->title }}</td>
                    <td>{{ $document->filename }}</td>
                    <td>{{ strtoupper($document->file_type) }}</td>
                    <td>{{ $document->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('documents.edit', $document->id) }}" class="btn btn-sm btn-warning">Edit</a>
                        <a href="{{ route('documents.download', $document->id) }}" class="btn btn-sm btn-info">Download</a>
                        <form action="{{ route('documents.destroy', $document->id) }}" method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin hapus dokumen ini?')">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center">Belum ada dokumen</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
