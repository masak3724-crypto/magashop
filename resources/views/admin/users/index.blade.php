@extends('admin.layouts.app')
@section('title', 'Пользователи')
@section('page_title', 'Пользователи')

@section('content')
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-user-shield me-2 text-accent"></i>Администраторы</h5>
        <button class="btn btn-sm btn-primary-custom" type="button" data-bs-toggle="collapse" data-bs-target="#adminForm">
            {{ request()->has('admin') || $errors->any() ? 'Свернуть' : 'Добавить админа' }}
        </button>
    </div>
    <div class="collapse {{ request()->has('admin') || $errors->any() ? 'show' : '' }}" id="adminForm">
        <div class="card-body border-top">
            <form action="{{ route('admin.users.admin.store') }}" method="POST" id="admin-user-form">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Действие</label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mode" id="mode-create" value="create" @checked(old('mode', 'create') === 'create')>
                            <label class="form-check-label" for="mode-create">Создать нового администратора</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="mode" id="mode-promote" value="promote" @checked(old('mode') === 'promote')>
                            <label class="form-check-label" for="mode-promote">Назначить админом существующего пользователя</label>
                        </div>
                    </div>
                </div>

                <div id="admin-create-fields" class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Имя</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Иван Админов">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="admin@example.ru">
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Пароль</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Подтверждение пароля</label>
                        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password">
                    </div>
                </div>

                <div id="admin-promote-fields" class="row g-3 d-none">
                    <div class="col-md-6">
                        <label class="form-label">Пользователь</label>
                        <select name="user_id" class="form-select @error('user_id') is-invalid @enderror">
                            <option value="">Выберите пользователя...</option>
                            @foreach($promotableUsers as $candidate)
                            <option value="{{ $candidate->id }}" @selected(old('user_id') == $candidate->id)>
                                {{ $candidate->name }} ({{ $candidate->email }})
                            </option>
                            @endforeach
                        </select>
                        @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @if($promotableUsers->isEmpty())
                        <div class="form-text">Нет пользователей без прав администратора.</div>
                        @endif
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-accent">
                        <i class="fas fa-user-shield me-1"></i> Сохранить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-6">
                <label class="form-label small text-muted">Поиск по имени или email</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Имя, email...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary-custom">Найти</button>
                @if(request('q'))
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Сброс</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Email</th>
                        <th>Телефон</th>
                        <th>Заказов</th>
                        <th>Регистрация</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>
                            {{ $user->name }}
                            @if($user->is_admin)
                            <span class="badge bg-dark ms-1">Админ</span>
                            @endif
                        </td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->profile?->phone ?? '—' }}</td>
                        <td>{{ $user->orders_count }}</td>
                        <td>{{ $user->created_at->format('d.m.Y') }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary">Подробнее</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">Пользователи не найдены</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($users->hasPages())
    <div class="card-footer bg-white">{{ $users->links() }}</div>
    @endif
</div>
@endsection

@push('scripts')
<script>
(function () {
    const modeCreate = document.getElementById('mode-create');
    const modePromote = document.getElementById('mode-promote');
    const createFields = document.getElementById('admin-create-fields');
    const promoteFields = document.getElementById('admin-promote-fields');

    function toggleAdminForm() {
        const promote = modePromote.checked;
        createFields.classList.toggle('d-none', promote);
        promoteFields.classList.toggle('d-none', !promote);
    }

    modeCreate.addEventListener('change', toggleAdminForm);
    modePromote.addEventListener('change', toggleAdminForm);
    toggleAdminForm();
})();
</script>
@endpush
