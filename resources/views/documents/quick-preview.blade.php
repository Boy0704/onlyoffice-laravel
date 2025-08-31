@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Quick Preview: {{ $document->title }}</h3>
            <div>
                <a href="{{ route('documents.preview', $document->id) }}" class="btn btn-info">Full Preview</a>
                <a href="{{ route('documents.edit', $document->id) }}" class="btn btn-warning">Edit</a>
                <a href="{{ route('documents.download', $document->id) }}" class="btn btn-success">Download</a>
                <a href="{{ route('documents.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>

        <div class="alert alert-info">
            <strong>Quick Preview Mode</strong> - Menggunakan Microsoft Office Online Viewer.
            Untuk fitur lengkap, gunakan <a href="{{ route('documents.preview', $document->id) }}">Full Preview</a>.
        </div>

        <div style="position: relative; padding-bottom: 75%; height: 0; overflow: hidden;">
            <iframe
                src="{{ $viewerUrl }}"
                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: 1px solid #ddd;"
                frameborder="0">
            </iframe>
        </div>
    </div>
</div>
@endsection
