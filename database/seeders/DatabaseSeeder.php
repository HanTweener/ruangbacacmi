<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Akun demo untuk pengujian & presentasi
        $author = User::firstOrCreate(['email' => 'redaksi@example.test'], [
            'name' => 'Redaksi Demo CMI',
            'password' => 'Password123456',
        ]);
        $second = User::firstOrCreate(['email' => 'penulis@example.test'], [
            'name' => 'Penulis Demo',
            'password' => 'Password123456',
        ]);
        $stories = [
            [
                'Memberi ruang untuk jeda di tengah hari yang sibuk',
                'Gaya Hidup',
                'Selepas rapat, meja kerja masih dipenuhi catatan. Saya menutup laptop sebentar dan merapikan kertas sebelum beralih ke tugas berikutnya.'
                    ."\n\n".'Jeda hari itu tidak terjadwal panjang. Cukup untuk melihat daftar pekerjaan lagi dan memilih satu hal yang perlu diselesaikan lebih dulu.',
            ],
            [
                'Membaca label pangan: mulai dari rasa ingin tahu',
                'Nutrisi',
                'Dua kemasan biskuit dengan ukuran serupa ternyata mencantumkan jumlah sajian yang berbeda. Saya baru menyadarinya ketika membaca bagian belakang kemasan.'
                    ."\n\n".'Kini saya membandingkan informasi dengan memperhatikan takaran saji, daftar bahan, dan tanggal kedaluwarsa, bukan hanya gambar di bagian depan.',
            ],
            [
                'Catatan kecil sebelum kunjungan ke dokter',
                'Kesehatan',
                'Menjelang kunjungan, saya menuliskan pertanyaan yang sering terlupa saat sudah berada di ruang konsultasi. Catatan itu saya simpan bersama informasi jadwal dan lokasi.'
                    ."\n\n".'Saya juga mencatat hal yang ingin diceritakan dengan urutan waktu. Dengan begitu, percakapan bisa dimulai dari catatan yang sudah tersedia.',
            ],
            [
                'Menemukan kembali kesenangan berjalan kaki',
                'Gaya Hidup',
                'Jalan menuju warung yang biasanya saya lewati dengan kendaraan terasa berbeda ketika ditempuh dengan berjalan kaki. Ada toko buku kecil yang selama ini luput dari perhatian.'
                    ."\n\n".'Perjalanan pulang jadi sedikit lebih lama karena saya berhenti melihat etalasenya. Catatan hari itu justru tentang tempat baru yang ditemukan di rute lama.',
            ],
            [
                'Warna-warni di meja makan, cerita di setiap piring',
                'Nutrisi',
                'Makan siang keluarga kali ini berisi tumis wortel, sayur bayam, dan tempe. Warna di meja mengingatkan kami pada menu yang biasa dimasak di rumah nenek.'
                    ."\n\n".'Kami menulis ulang resepnya, termasuk bahan yang diganti karena tidak tersedia. Catatan resep itu sekarang memuat versi tiap anggota keluarga.',
            ],
            [
                'Mengenal kebiasaan diri lewat jurnal harian',
                'Kesehatan',
                'Buku catatan saya hanya memiliki tiga kolom: kegiatan, waktu, dan hal yang ingin diingat. Tidak setiap hari terisi, tetapi formatnya cukup mudah dilanjutkan.'
                    ."\n\n".'Saat membaca halaman minggu lalu, saya menemukan beberapa kegiatan yang selalu tertunda. Saya menandainya untuk ditinjau kembali saat menyusun jadwal berikutnya.',
            ],
            [
                'Ruang berbagi: cerita dari komunitas pembaca',
                'Kegiatan',
                'Pertemuan kelompok baca dimulai dengan satu aturan sederhana: setiap orang membawa satu kutipan dari buku pilihannya. Peserta kemudian bergiliran menjelaskan alasan memilih kutipan itu.'
                    ."\n\n".'Di akhir pertemuan, kami mengumpulkan daftar judul yang disebutkan. Daftar tersebut menjadi pilihan bacaan untuk diskusi bulan berikutnya.',
            ],
            [
                'Menata sudut rumah untuk waktu beristirahat',
                'Gaya Hidup',
                'Sebuah kursi dekat jendela tadinya dipakai untuk menumpuk tas. Setelah tas dipindahkan, tempat itu cukup untuk kursi, lampu baca, dan meja kecil.'
                    ."\n\n".'Saya mencoba beberapa posisi lampu agar halaman buku terlihat jelas. Barang yang tidak dipakai dikembalikan ke lemari, sehingga meja tetap bisa digunakan.',
            ],
            [
                'Belanja dengan daftar, memasak dengan gembira',
                'Nutrisi',
                'Sebelum pergi ke pasar, kami memeriksa isi kulkas dan menuliskan bahan untuk tiga menu. Tomat dan bawang yang masih tersedia dicoret dari daftar belanja.'
                    ."\n\n".'Sesampainya di pasar, satu bahan ternyata habis. Kami mengganti menu dan mencatat perubahan itu agar bahan yang sudah dibeli tetap terpakai.',
            ],
            [
                'Menyimpan pertanyaan untuk percakapan yang lebih baik',
                'Kesehatan',
                'Setelah menerima informasi jadwal pemeriksaan, saya masih memiliki pertanyaan tentang alur pendaftaran dan dokumen yang perlu dibawa. Semuanya saya tulis dalam satu catatan.'
                    ."\n\n".'Saat menghubungi bagian administrasi, saya menanyakan setiap poin dan mencatat jawabannya. Catatan tersebut membantu saya membedakan informasi yang sudah jelas dan yang masih perlu dikonfirmasi.',
            ],
            [
                'Satu buku, banyak sudut pandang',
                'Kegiatan',
                'Kami membaca cerpen yang sama, tetapi membicarakan tokoh yang berbeda. Ada yang memperhatikan percakapan pembuka, sementara yang lain tertarik pada keputusan tokoh di akhir cerita.'
                    ."\n\n".'Diskusi berlangsung dengan merujuk kembali ke halaman buku. Beberapa bagian yang terlewat saat membaca sendiri justru menjadi bahan pembicaraan paling lama.',
            ],
            [
                'Melihat pagi dari jendela yang berbeda',
                'Gaya Hidup',
                'Meja makan dipindahkan ke dekat jendela setelah ruang depan ditata ulang. Dari tempat duduk baru, saya bisa melihat penjual sarapan mulai membuka lapaknya.'
                    ."\n\n".'Saya mencatat perubahan kecil di jalan selama beberapa pagi: payung yang berganti warna, jam buka warung, dan tanaman di teras tetangga.',
            ],
        ];
        foreach ($stories as $index => [$title, $category, $body]) {
            Article::firstOrCreate(['slug' => Str::slug($title)], [
                'user_id' => $index % 3 === 0 ? $second->id : $author->id,
                'judul' => $title, 'kategori' => $category, 'status' => true,
                'isi' => $body,
                'created_at' => now()->subDays($index), 'updated_at' => now()->subDays($index),
            ]);
        }
        Article::firstOrCreate(['slug' => 'catatan-draft-demo'], ['user_id' => $author->id, 'judul' => 'Catatan yang belum selesai', 'isi' => 'Ini contoh draft sintetis. Konten dan gambar draft tidak boleh terlihat oleh pengunjung atau penulis lain.', 'status' => false, 'kategori' => 'Kesehatan']);
    }
}
