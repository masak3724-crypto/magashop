@extends('shop.layouts.app')
@section('title', 'Редактирование профиля')
@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card shadow-sm p-4">
                <h3 class="mb-4">Редактировать профиль</h3>
                <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="profile-form" data-cities='@json($cities)' data-phone-countries='@json($phoneCountries)' data-phone-country="{{ $phoneCountryCode }}" data-phone-national="{{ $phoneNational }}" novalidate>
                    @csrf
                    <div class="mb-3 phone-field">
                        <label class="form-label">Телефон</label>
                        <div class="phone-input-wrap">
                            <div class="dropdown phone-country-dropdown">
                                <button class="btn phone-country-toggle dropdown-toggle" type="button" data-bs-toggle="dropdown" data-bs-auto-close="true" aria-expanded="false">
                                    <span class="phone-country-flag" id="phone-country-flag">🇷🇺</span>
                                    <span class="phone-country-dial" id="phone-country-dial">+7</span>
                                </button>
                                <ul class="dropdown-menu phone-country-menu shadow">
                                    @foreach($phoneCountries as $country)
                                    <li>
                                        <button type="button" class="dropdown-item phone-country-option" data-code="{{ $country['code'] }}">
                                            <span class="phone-country-option-flag">{{ $country['flag'] }}</span>
                                            <span class="phone-country-option-name">{{ $country['name'] }}</span>
                                            <span class="phone-country-option-dial">+{{ $country['dial'] }}</span>
                                        </button>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="phone-input-group flex-grow-1">
                                <input type="tel" class="form-control phone-national-input" id="phone-national-input" inputmode="numeric" autocomplete="tel-national" placeholder="">
                                <input type="hidden" name="phone" id="phone-full-input" value="{{ old('phone', $profile->phone) }}">
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Город</label>
                        <input type="text" name="city" class="form-control" value="{{ old('city', $profile->city) }}" list="profile-cities-list" autocomplete="off" placeholder="Начните вводить город">
                        <datalist id="profile-cities-list">
                            @foreach($cities as $cityName)
                            <option value="{{ $cityName }}"></option>
                            @endforeach
                        </datalist>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Адрес</label>
                        <textarea name="address" class="form-control" rows="2" placeholder="ул. Примерная, д. 10, кв. 5">{{ old('address', $profile->address) }}</textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Почтовый индекс</label>
                        <input type="text" name="postal_code" class="form-control" value="{{ old('postal_code', $profile->postal_code) }}" inputmode="numeric" maxlength="6" placeholder="000000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Аватар</label>
                        <input type="file" name="avatar" class="form-control" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-accent">Сохранить</button>
                    <a href="{{ route('profile') }}" class="btn btn-outline-secondary">Отмена</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/shop-forms.js') }}"></script>
@endpush
