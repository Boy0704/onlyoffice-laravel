@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-6">
        <h2>Upload Dokumen Baru</h2>

        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label for="title" class="form-label">Judul Dokumen</label>
                <input type="text" class="form-control @error('title') is-invalid @enderror"
                       id="title" name="title" value="{{ old('title') }}" required>
                @error('title')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="file" class="form-label">File Dokumen</label>
                <input type="file" class="form-control @error('file') is-invalid @enderror"
                       id="file" name="file" accept=".doc,.docx,.xls,.xlsx,.ppt,.pptx" required>
                <div class="form-text">Format: DOC, DOCX, XLS, XLSX, PPT, PPTX (Max: 10MB)</div>
                @error('file')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Upload</button>
            <a href="{{ route('documents.index') }}" class="btn btn-secondary">Batal</a>
        </form>
    </div>
</div>
@endsection
