<?php

return [
    'required' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'email' => 'Masukkan alamat email yang valid.',
    'unique' => ':attribute sudah digunakan.',
    'confirmed' => 'Konfirmasi :attribute tidak sesuai.',
    'lowercase' => ':attribute harus menggunakan huruf kecil.',
    'boolean' => 'Pilihan :attribute tidak valid.',
    'in' => 'Pilihan :attribute tidak valid.',
    'image' => ':attribute harus berupa gambar yang valid.',
    'uploaded' => ':attribute gagal diunggah. Periksa ukuran file, lalu pilih ulang file.',
    'mimes' => ':attribute harus berupa file: :values.',
    'dimensions' => 'Dimensi :attribute tidak boleh melebihi 5000 × 5000 piksel.',
    'min' => ['string' => ':attribute minimal :min karakter.'],
    'max' => ['string' => ':attribute maksimal :max karakter.', 'file' => ':attribute maksimal :max KB.'],
    'password' => ['letters' => ':attribute harus mengandung huruf.', 'numbers' => ':attribute harus mengandung angka.'],
    'attributes' => ['name' => 'Nama', 'email' => 'Email', 'password' => 'Kata sandi'],
];
