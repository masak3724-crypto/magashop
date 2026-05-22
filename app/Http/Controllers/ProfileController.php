<?php

namespace App\Http\Controllers;

use App\Support\PhoneCountries;
use App\Support\RussianCities;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        $user = auth()->user()->load('profile');
        $orders = $user->orders()->latest()->get();

        return view('shop.profile', compact('user', 'orders'));
    }

    public function edit()
    {
        $profile = auth()->user()->profile ?? auth()->user()->profile()->create([]);

        $phoneParsed = PhoneCountries::parse($profile->phone);

        return view('shop.edit_profile', [
            'profile' => $profile,
            'cities' => RussianCities::all(),
            'phoneCountries' => PhoneCountries::all(),
            'phoneCountryCode' => $phoneParsed['country']['code'] ?? 'RU',
            'phoneNational' => $phoneParsed['national'] ?? '',
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'phone' => ['nullable', 'string', 'max:30', function ($attribute, $value, $fail) {
                if ($value !== null && $value !== '' && ! PhoneCountries::isValid($value)) {
                    $fail('Укажите телефон полностью в формате выбранной страны.');
                }
            }],
            'city' => ['nullable', 'string', Rule::in(RussianCities::all())],
            'address' => ['nullable', 'string', 'min:5', 'max:250', 'regex:/^[а-яА-ЯёЁa-zA-Z0-9\s.,\-\/№]+$/u'],
            'postal_code' => ['nullable', 'digits:6'],
            'avatar' => 'nullable|image|max:2048',
        ], [
            'city.in' => 'Выберите город из списка.',
            'postal_code.digits' => 'Почтовый индекс должен содержать ровно 6 цифр.',
            'address.min' => 'Адрес слишком короткий.',
            'address.regex' => 'Адрес содержит недопустимые символы.',
        ]);

        if (! empty($data['city'])) {
            $data['city'] = RussianCities::normalize($data['city']) ?? $data['city'];
        }

        if (($data['phone'] ?? '') === '') {
            $data['phone'] = null;
        }

        $profile = auth()->user()->profile ?? auth()->user()->profile()->create([]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = $path;
        }

        $profile->update($data);

        return redirect()->route('profile')->with('success', 'Профиль обновлён');
    }
}
