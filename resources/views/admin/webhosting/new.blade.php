@extends('layouts.admin')

@section('title')
    New Website
@endsection

@section('content-header')
    <h1>New Website<small>Create a website and generate nginx config.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.webhosting') }}">Web Hosting</a></li>
        <li class="active">New</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Website Details</h3>
                </div>
                <form action="{{ route('admin.webhosting.new') }}" method="POST">
                    {!! csrf_field() !!}
                    <div class="box-body">
                        <div class="form-group">
                            <label class="control-label">Owner</label>
                            <select name="user_id" class="form-control">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->username }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Domain</label>
                            <input type="text" name="domain" class="form-control" value="{{ old('domain') }}" />
                            <p class="text-muted small">Example: example.com</p>
                        </div>
                        <div class="form-group">
                            <label class="control-label">Document Root</label>
                            <input type="text" name="root_path" class="form-control" value="{{ old('root_path', '/var/www/example') }}" />
                        </div>
                        <div class="form-group">
                            <label class="control-label">PHP Version (optional)</label>
                            <input type="text" name="php_version" class="form-control" value="{{ old('php_version') }}" />
                        </div>
                        <div class="form-group">
                            <div class="checkbox checkbox-primary">
                                <input id="enableSsl" type="checkbox" name="enable_ssl" value="1" />
                                <label for="enableSsl"> Issue SSL with Let's Encrypt</label>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer">
                        <button class="btn btn-primary">Create Website</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

