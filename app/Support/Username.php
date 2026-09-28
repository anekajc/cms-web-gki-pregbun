<?php

namespace App\Support;

use Illuminate\Validation\Rule;

/**
 * Username rules, shared by the first-login page and Profile settings.
 * Usernames never contain "@", so the login field can tell them apart from
 * emails; they are stored lowercase, making login case-insensitive.
 */
class Username
{
    public static function normalize(mixed $value): mixed
    {
        return is_string($value) ? mb_strtolower(trim($value)) : $value;
    }

    /**
     * @return list<mixed>
     */
    public static function rules(?int $ignoreUserId = null): array
    {
        return [
            'required',
            'string',
            'min:3',
            'max:30',
            'regex:/^[a-z0-9._-]+$/',
            Rule::unique('users', 'username')->ignore($ignoreUserId),
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function messages(): array
    {
        return [
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 30 karakter.',
            'username.regex' => 'Username hanya boleh berisi huruf, angka, titik (.), garis bawah (_), atau tanda hubung (-), tanpa spasi.',
            'username.unique' => 'Username sudah dipakai. Pilih username lain.',
        ];
    }
}
