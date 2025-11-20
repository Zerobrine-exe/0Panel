@extends('layouts.admin')

@section('title')
    Website — {{ $website->domain }}
@endsection

@section('content-header')
    <h1>{{ $website->domain }}<small>Manage nginx and SSL.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li><a href="{{ route('admin.webhosting') }}">Web Hosting</a></li>
        <li class="active">{{ $website->domain }}</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-sm-8">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Details</h3>
                </div>
                <div class="box-body">
                    <dl class="dl-horizontal">
                        <dt>Owner</dt>
                        <dd><a href="{{ route('admin.users.view', $website->user_id) }}">{{ $website->user->username }}</a></dd>
                        <dt>Domain</dt>
                        <dd>{{ $website->domain }}</dd>
                        <dt>Document Root</dt>
                        <dd><code>{{ $website->root_path }}</code></dd>
                        <dt>SSL</dt>
                        <dd>{{ $website->ssl_enabled ? 'Enabled' : 'Disabled' }} ({{ $website->ssl_status }})</dd>
                        <dt>Config Path</dt>
                        <dd><code>{{ $website->nginx_config_path }}</code></dd>
                        <dt>Status</dt>
                        <dd>{{ $website->enabled ? 'Active' : 'Disabled' }}</dd>
                    </dl>
                </div>
                <div class="box-footer">
                    <form action="{{ route('admin.webhosting.reprovision', $website->id) }}" method="POST" style="display:inline-block">
                        {!! csrf_field() !!}
                        <button class="btn btn-default">Re-provision Nginx</button>
                    </form>
                    <form action="{{ route('admin.webhosting.issueSsl', $website->id) }}" method="POST" style="display:inline-block">
                        {!! csrf_field() !!}
                        <button class="btn btn-primary" {{ $website->ssl_status === 'active' ? 'disabled' : '' }}>Issue SSL</button>
                    </form>
                    <form action="{{ route('admin.webhosting.toggle', $website->id) }}" method="POST" style="display:inline-block">
                        {!! csrf_field() !!}
                        <button class="btn btn-warning">{{ $website->enabled ? 'Disable' : 'Enable' }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

