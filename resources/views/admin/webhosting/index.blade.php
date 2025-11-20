@extends('layouts.admin')

@section('title')
    Web Hosting
@endsection

@section('content-header')
    <h1>Web Hosting<small>Manage hosted websites and SSL.</small></h1>
    <ol class="breadcrumb">
        <li><a href="{{ route('admin.index') }}">Admin</a></li>
        <li class="active">Web Hosting</li>
    </ol>
@endsection

@section('content')
    <div class="row">
        <div class="col-xs-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Websites</h3>
                    <div class="box-tools">
                        <a href="{{ route('admin.webhosting.new') }}" class="btn btn-sm btn-primary">Create New</a>
                    </div>
                </div>
                <div class="box-body table-responsive no-padding">
                    <table class="table table-hover">
                        <tr>
                            <th>ID</th>
                            <th>Domain</th>
                            <th>Owner</th>
                            <th>SSL</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                        @foreach($websites as $site)
                            <tr>
                                <td><code>{{ $site->uuid }}</code></td>
                                <td><a href="{{ route('admin.webhosting.view', $site->id) }}">{{ $site->domain }}</a></td>
                                <td><a href="{{ route('admin.users.view', $site->user_id) }}">{{ $site->user->username }}</a></td>
                                <td>{{ $site->ssl_enabled ? 'Enabled' : 'Disabled' }} ({{ $site->ssl_status }})</td>
                                <td>{{ $site->enabled ? 'Active' : 'Disabled' }}</td>
                                <td><a class="btn btn-xs btn-default" href="{{ route('admin.webhosting.view', $site->id) }}">Manage</a></td>
                            </tr>
                        @endforeach
                    </table>
                </div>
                @if($websites->hasPages())
                    <div class="box-footer with-border">
                        <div class="col-md-12 text-center">{!! $websites->render() !!}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

