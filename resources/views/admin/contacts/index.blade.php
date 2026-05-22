@extends('admin.layouts.app')
@section('title', 'Сообщения')
@section('page_title', 'Обращения с сайта')

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body d-flex gap-2">
        <a href="{{ route('admin.contacts.index') }}" class="btn btn-sm {{ !request('filter') ? 'btn-primary-custom' : 'btn-outline-secondary' }}">Все</a>
        <a href="{{ route('admin.contacts.index', ['filter' => 'unread']) }}" class="btn btn-sm {{ request('filter') === 'unread' ? 'btn-primary-custom' : 'btn-outline-secondary' }}">Непрочитанные</a>
    </div>
</div>

<div class="row g-3">
    @forelse($messages as $message)
    <div class="col-12">
        <div class="card shadow-sm border-0 {{ !$message->is_processed ? 'border-start border-4 border-warning' : '' }}">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-2">
                    <div>
                        <strong>{{ $message->name }}</strong>
                        <span class="text-muted ms-2">{{ $message->email }}</span>
                        @if($message->phone)
                        <span class="text-muted ms-2">{{ $message->phone }}</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="small text-muted">{{ $message->created_at->format('d.m.Y H:i') }}</span>
                        @if($message->is_processed)
                        <span class="badge bg-success">Обработано</span>
                        @else
                        <span class="badge bg-warning text-dark">Новое</span>
                        @endif
                    </div>
                </div>
                <p class="mb-3">{{ $message->message }}</p>
                <form action="{{ route('admin.contacts.toggle', $message) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm {{ $message->is_processed ? 'btn-outline-secondary' : 'btn-accent' }}">
                        {{ $message->is_processed ? 'Отметить как новое' : 'Отметить обработанным' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <p class="text-muted text-center py-5">Сообщений нет</p>
    </div>
    @endforelse
</div>

@if($messages->hasPages())
<div class="mt-3">{{ $messages->links() }}</div>
@endif
@endsection
