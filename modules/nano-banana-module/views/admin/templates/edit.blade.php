@extends('layouts.admin')

@section('title', 'Edit AI Image Template')
@section('page-title', 'Edit AI Image Template')

@section('content')
<div class="my-4">
    <div class="mb-4">
        <a href="{{ route('admin.nanobanana.templates.index') }}" class="text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i>Back to Templates
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="{{ route('admin.nanobanana.templates.update', $template) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('nano-banana-module::admin.templates._form', ['template' => $template])
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i>Update Template
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
