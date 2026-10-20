<?php

namespace Database\Seeders;

use App\Models\Answer;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CustomerServiceDealmakerSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(fn (): Course => $this->seedCourse());
    }

    public function seedCourse(): Course
    {
        $category = CourseCategory::query()->firstOrCreate(
            ['name' => 'Layanan Pelanggan'],
            ['is_active' => true],
        );

        $course = Course::query()->firstOrCreate(
            ['code' => 'CS-DEAL-001'],
            [
                'category_id' => $category->id,
                'title' => 'Customer Service Dealmaker Mastery',
                'description' => 'Program penguatan kemampuan customer service, product knowledge, dan strategi penjualan perhiasan.',
                'estimated_minutes' => 300,
                'is_mandatory' => false,
                'requires_post_test' => false,
                'certificate_enabled' => false,
                'passing_grade' => 70,
                'status' => 'published',
                'published_at' => now(),
            ],
        );

        foreach ($this->modules() as $moduleSequence => $moduleData) {
            $module = CourseModule::query()->firstOrCreate(
                ['course_id' => $course->id, 'sequence' => $moduleSequence + 1],
                ['title' => $moduleData['title']],
            );

            Lesson::query()->firstOrCreate(
                ['module_id' => $module->id, 'sequence' => 1],
                [
                    'title' => 'Presentasi '.$moduleData['title'],
                    'type' => 'presentation',
                    'content' => null,
                    'transcript' => null,
                    'resource_url' => $moduleData['presentation_url'],
                    'is_required' => true,
                ],
            );
            Lesson::query()->firstOrCreate(
                ['module_id' => $module->id, 'sequence' => 2],
                [
                    'title' => 'Video '.$moduleData['title'],
                    'type' => 'video',
                    'content' => null,
                    'transcript' => null,
                    'resource_url' => $moduleData['video_url'],
                    'is_required' => true,
                ],
            );

            $this->seedModuleAssessments($course, $module, $moduleSequence + 1);
        }

        return $course;
    }

    private function seedModuleAssessments(Course $course, CourseModule $module, int $moduleSequence): void
    {
        foreach ($this->assessmentQuestions()[$moduleSequence] ?? [] as $type => $questions) {
            $assessment = Assessment::query()->firstOrCreate(
                ['course_id' => $course->id, 'module_id' => $module->id, 'type' => $type],
                [
                    'is_required' => true,
                    'passing_grade' => $type === 'pre_test' ? 0 : 80,
                    'attempt_limit' => $type === 'pre_test' ? 1 : 3,
                    'time_limit_minutes' => $type === 'pre_test' ? 10 : 15,
                ],
            );

            if (! $assessment->wasRecentlyCreated) {
                continue;
            }

            foreach ($questions as $sequence => $questionData) {
                $question = Question::query()->create([
                    'assessment_id' => $assessment->id,
                    'prompt' => $questionData['prompt'],
                    'explanation' => $questionData['explanation'],
                    'points' => 1,
                    'sequence' => $sequence + 1,
                ]);

                foreach ($questionData['answers'] as $answerSequence => $answer) {
                    Answer::query()->create([
                        'question_id' => $question->id,
                        'content' => $answer,
                        'is_correct' => $answerSequence === $questionData['correct'],
                        'sequence' => $answerSequence + 1,
                    ]);
                }
            }
        }
    }

    /** @return array<int, array<string, array<int, array{prompt: string, explanation: string, correct: int, answers: array<int, string>}>>> */
    private function assessmentQuestions(): array
    {
        return [
            1 => [
                'pre_test' => [
                    $this->q('Peran utama CS Dealmaker adalah…', 'CS Dealmaker menjembatani kebutuhan customer dengan solusi sampai kesepakatan.', 1, ['Menerima keluhan tanpa menawarkan solusi', 'Mengarahkan kebutuhan customer ke solusi hingga transaksi', 'Membuat laporan keuangan perusahaan', 'Menggantikan seluruh tugas marketing']),
                    $this->q('Perbedaan utama CS biasa dan CS Dealmaker adalah…', 'CS biasa cenderung reaktif, sedangkan Dealmaker proaktif mengarahkan percakapan.', 2, ['Tidak ada perbedaan', 'CS biasa selalu melakukan closing', 'Dealmaker mengarahkan percakapan menuju penjualan', 'Dealmaker tidak perlu menjawab pertanyaan']),
                    $this->q('Filosofi CS Dealmaker yang tepat adalah…', 'Pendekatan yang benar membantu customer membeli, bukan memaksa.', 0, ['Membantu orang membeli, bukan memaksa', 'Menutup transaksi secepat mungkin', 'Memberi diskon kepada semua orang', 'Menghindari semua pertanyaan customer']),
                    $this->q('Lead qualification digunakan untuk…', 'Lead qualification menyaring calon pembeli yang benar-benar membutuhkan solusi.', 3, ['Menghapus semua lead lama', 'Membuat promo massal', 'Menentukan komisi sales', 'Menyaring calon pembeli yang relevan']),
                    $this->q('Informasi produk harus diberikan dengan sikap…', 'Integritas berarti menyampaikan informasi jujur tanpa melebih-lebihkan produk.', 1, ['Melebih-lebihkan agar cepat closing', 'Jujur dan tidak berlebihan', 'Menutup kekurangan produk', 'Menjelekkan kompetitor']),
                ],
                'post_test' => [
                    $this->q('CS menjawab spesifikasi dengan ramah tetapi tidak mengarahkan percakapan ke transaksi. Evaluasinya?', 'Dealmaker tetap perlu membawa percakapan menuju solusi dan keputusan pembelian.', 1, ['Sudah tepat karena informasi lengkap', 'Masih reaktif, belum bertindak sebagai Dealmaker', 'Sudah closing karena customer teredukasi', 'Harus langsung memaksa pembayaran']),
                    $this->q('Tahap Lead Qualification yang tepat adalah…', 'Tahap ini menyaring calon pembeli yang benar-benar membutuhkan produk.', 2, ['Menawarkan diskon ke semua kontak', 'Mengawal pembayaran hingga dana masuk', 'Memisahkan calon pembeli yang membutuhkan solusi', 'Memberikan rekomendasi setelah keluhan']),
                    $this->q('Hak CS Dealmaker berikut yang BUKAN haknya adalah…', 'Data keuangan rahasia seluruh perusahaan bukan hak akses CS.', 0, ['Akses penuh data keuangan rahasia perusahaan', 'Informasi produk terbaru', 'Tools CRM dan template chat', 'Insentif sesuai kebijakan dan performa']),
                    $this->q('Tidak melebih-lebihkan fungsi produk demi closing menerapkan kewajiban…', 'Kejujuran informasi produk adalah bentuk integritas.', 2, ['Responsivitas', 'Kerapihan data', 'Integritas', 'Akuntabilitas']),
                    $this->q('Mengapa reporting konversi yang rapi penting?', 'Data konversi digunakan untuk evaluasi strategi marketing.', 0, ['Menjadi dasar evaluasi strategi marketing', 'Menjual data customer ke pihak lain', 'Sekadar formalitas harian', 'Membuat customer memblokir kontak']),
                    $this->q('Langkah awal pola Tanya-Tanya-Jual saat customer baru menyapa adalah…', 'CS perlu menggali masalah dan kebutuhan sebelum menawarkan solusi.', 2, ['Mengirim pricelist dan promo', 'Mengirim testimoni terlebih dahulu', 'Mengajukan pertanyaan tentang kebutuhan customer', 'Menanyakan metode pembayaran']),
                    $this->q('Penerapan follow-up 3 x 24 jam yang benar adalah…', 'Follow-up dilakukan hari pertama, kedua, dan ketiga dengan gaya berbeda.', 1, ['Pesan sama tiga kali dalam sehari', 'Hari 1, 2, dan 3 dengan variasi gaya bahasa', 'Chat tanpa henti selama tiga hari', 'Menunggu tiga hari tanpa menghubungi']),
                    $this->q('Saat customer membandingkan kompetitor yang lebih murah, respons yang tepat adalah…', 'Fokus pada nilai tambah produk sendiri tanpa menjelekkan kompetitor.', 2, ['Menyarankan membeli di kompetitor', 'Menjelaskan kelemahan kompetitor', 'Menjelaskan nilai tambah produk sendiri', 'Mengabaikan pertanyaan']),
                    $this->q('Mengapa template chat tidak boleh di-copy-paste tanpa edit?', 'Customer dapat merasa diperlakukan sebagai nomor, bukan manusia.', 0, ['Customer merasa hanya dianggap nomor', 'CRM akan otomatis mengunci akun', 'Kuota chat cepat habis', 'Menyalin selalu lebih lama']),
                    $this->q('Kalimat yang tepat untuk menciptakan urgency adalah…', 'Magic words memakai urgensi yang relevan sambil tetap menawarkan solusi.', 2, ['Stok selalu ribuan dan bebas dipikirkan', 'Produk tidak bagus tetapi murah', 'Solusi tepat dan hanya tersisa dua slot promo', 'Ketik angka untuk membeli atau batal']),
                ],
            ],
            2 => [
                'pre_test' => [
                    $this->q('Material yang tepat untuk customer berkulit sensitif adalah…', 'Whiterock Platinum 950 dikenal hipoalergenik dan aman untuk kulit sensitif.', 3, ['Silver biasa', 'Yellow Gold 6K', 'Premium White Gold 6K', 'Whiterock Platinum 950']),
                    $this->q('Ciri khas Signature White Gold adalah…', 'Signature White Gold memakai emas murni dengan campuran palladium dan silver.', 1, ['Campuran emas dan tembaga saja', 'Emas diputihkan dengan palladium dan silver', 'Platinum murni tanpa alloy', 'Palladium 50 persen']),
                    $this->q('Finishing doff atau matte memberi kesan…', 'Doff tidak memantulkan cahaya dan memberi kesan modern serta minimalis.', 2, ['Sangat berkilau seperti cermin', 'Tradisional dengan ukiran tajam', 'Modern dan tidak terlalu mencolok', 'Selalu penuh tekstur palu']),
                    $this->q('Model Simple paling cocok untuk…', 'Model simple praktis untuk aktivitas harian dan cocok bagi penyuka konsep less is more.', 0, ['Customer aktif yang menyukai desain praktis', 'Customer yang hanya ingin pesta', 'Customer yang ingin ornamen budaya', 'Customer yang selalu mencari batu besar']),
                    $this->q('Tujuan memahami product knowledge bagi CS adalah…', 'CS dapat menyesuaikan material, desain, dan nilai tambah dengan kebutuhan customer.', 2, ['Menghafal seluruh harga tanpa konteks', 'Menolak semua permintaan custom', 'Memberi rekomendasi sesuai kebutuhan', 'Mengganti keputusan customer']),
                ],
                'post_test' => [
                    $this->q('Pria muslim mencari cincin putih tahan benturan dengan dana terbatas. Rekomendasi tepat?', 'Premium Palladium merupakan alternatif non-emas yang tahan lama dan lebih terjangkau.', 1, ['Whiterock Platinum 950', 'Premium Palladium 10% atau 25%', 'Blackrock Palladium 50%', 'Signature White Gold']),
                    $this->q('Customer aktif menyukai less is more dan tidak ingin cincin tersangkut. Rekomendasi tepat?', 'Model Simple dengan plain band atau flush setting praktis untuk aktivitas harian.', 1, ['Model Glamour dengan microsetting', 'Model Simple dengan plain band atau flush setting', 'Model Luxury dengan detail rumit', 'Nusantara Series bertekstur']),
                    $this->q('Customer kulit sensitif ingin kualitas emas putih terbaik. Paket paling sesuai?', 'Signature Package D memadukan Whiterock Platinum 950 dan Signature White Gold 18K.', 3, ['Lite Package', 'Premium Package A', 'Deluxe Package A', 'Signature Package D']),
                    $this->q('Finishing berkilau halus seperti butiran pasir tanpa pantulan cermin adalah…', 'Doff Foredom memberi kilau lembut menyerupai butiran pasir.', 2, ['Glossy dan hammered', 'Doff biasa', 'Doff Foredom', 'Pahat atau tatah']),
                    $this->q('Nilai tambah Paket Bundling dibanding pembelian satuan adalah…', 'Paket bundling menawarkan diskon langsung sehingga lebih hemat.', 1, ['Garansi otomatis 15 tahun', 'Diskon langsung setiap paket', 'Microsetting gratis 100 persen', 'Upgrade platinum otomatis']),
                    $this->q('Pesan urgensi yang benar untuk mulai produksi handmade adalah…', 'Harga dapat berubah dan produksi dimulai setelah DP minimal 80 persen.', 2, ['Harga logam selalu stabil', 'Produksi dimulai dengan DP 500 ribu', 'Harga dapat berubah dan DP minimal 80 persen', 'Harus memilih Signature Package']),
                    $this->q('Mengapa istilah pahat atau tatah penting untuk customer pencinta seni?', 'Istilah ini menonjolkan pengerjaan custom bernilai seni dari pengrajin.', 1, ['Menegaskan gaya maskulin', 'Menonjolkan pengerjaan custom bernilai seni', 'Finishing ini selalu berbayar', 'Dibuat mesin otomatis']),
                    $this->q('Anggaran maksimal 11 juta untuk palladium dan emas putih paling cocok dengan…', 'Lite Package berada dalam batas anggaran dan memadukan Palladium 10% serta Premium White Gold 6K.', 2, ['Standard Package A', 'Deluxe Package A', 'Lite Package', 'Premium Package A']),
                    $this->q('Fasilitas garansi yang BUKAN bagian dari layanan adalah…', 'Modul hanya menyebut resize, rhodium ulang, dan pasang mata lepas.', 0, ['Tukar tambah model baru gratis', 'Resize ukuran jari', 'Rhodium ulang', 'Pasang mata yang lepas']),
                    $this->q('Pernyataan tepat tentang Signature White Gold adalah…', 'Material ini memakai emas murni yang diputihkan palladium dan silver agar tidak mudah menguning.', 2, ['Emas 6K dengan alloy import', 'Platinum murni dengan silver', 'Emas diputihkan palladium dan silver', 'Palladium 950 murni']),
                ],
            ],
            3 => [
                'pre_test' => [
                    $this->q('Fokus komunikasi utama untuk customer Yogyakarta adalah…', 'Customer Yogyakarta menghargai edukasi dan narasi budaya.', 1, ['Diskon kilat tanpa penjelasan', 'Edukasi produk dan narasi budaya', 'Logistik ekspres saja', 'Kompetisi harga langsung']),
                    $this->q('Customer Semarang umumnya mengutamakan…', 'Segmen Practical Urban menyukai efisiensi, best value, dan keamanan pengiriman.', 0, ['Efisiensi dan paket paling bernilai', 'Cerita budaya panjang', 'Hanya kemasan premium', 'Produk paling mahal']),
                    $this->q('Customer Solo Mall sangat dipengaruhi oleh…', 'Segmen Solo Mall visual oriented dan brand conscious.', 2, ['Data donasi saja', 'Harga termurah', 'Display visual dan kemasan', 'Sejarah cabang']),
                    $this->q('Surabaya High-Tier Segment cocok ditawari…', 'Segmen ini memiliki luxury mindset dan terbuka pada Signature atau Platinum.', 3, ['Produk tanpa bonus', 'Hanya paket hemat', 'Model paling sederhana', 'Produk high-end secara percaya diri']),
                    $this->q('Peran CS di Bandung adalah…', 'Customer Bandung menghargai aesthetic advice dan komunikasi friendly.', 1, ['Operator transaksi singkat', 'Konsultan gaya yang friendly', 'Petugas logistik', 'Penilai kualitas produksi']),
                ],
                'post_test' => [
                    $this->q('Customer Semarang terburu-buru menanyakan paket paling menguntungkan. Respons awal tepat?', 'Gunakan pertanyaan singkat tentang budget lalu rekomendasikan best value.', 1, ['Kirim seluruh pricelist premium', 'Tanya singkat lalu rekomendasikan Best Value', 'Ceritakan sejarah pengrajin panjang', 'Menolak menjawab harga']),
                    $this->q('Nilai tambah utama untuk customer Solo Mall yang visual oriented adalah…', 'Display, finishing, dan kemasan eksklusif mendukung keputusan cepat di tempat.', 2, ['Diskon tunai saja', 'Gratis ongkir saja', 'Display, finishing, dan Box Exclusive', 'Cerita Nusantara Series']),
                    $this->q('Kesalahan fatal saat melayani luxury customer Surabaya adalah…', 'Segmen ini menghargai harga jujur dan transparansi kadar.', 1, ['Percaya diri saat konsultasi', 'Tidak transparan tentang harga atau kadar', 'Menawarkan Signature Package', 'Memberi bonus relevan']),
                    $this->q('Respons tepat untuk referensi Pinterest dari customer Bandung adalah…', 'CS perlu menjadi konsultan gaya dan melakukan follow-up yang personal.', 2, ['Mengirim template tanpa edit', 'Memaksa katalog standar', 'Memberi saran finishing lalu follow-up bervariasi', 'Menyuruh mencari toko lain']),
                    $this->q('Profil customer yang membawa referensi Instagram dan menyukai komunikasi akrab adalah…', 'Ciri tersebut sesuai dengan customer Bandung yang aesthetic oriented.', 1, ['Semarang', 'Bandung', 'Yogyakarta', 'Solo']),
                    $this->q('Fasilitas logistik yang perlu ditekankan pada customer Semarang adalah…', 'Customer Semarang memperhatikan gratis ongkir dan asuransi pengiriman.', 2, ['Sejarah Kotagede', 'Kristal Swarovski', 'Gratis ongkir dan asuransi', 'Custom shape']),
                    $this->q('Pendekatan tepat untuk value seeker di Yogyakarta adalah…', 'Customer Yogyakarta menghargai kualitas, proses, dan cerita pengrajin lokal.', 3, ['Menawarkan diskon tanpa konteks', 'Mempercepat tanpa edukasi', 'Fokus pada kemasan mall', 'Menjelaskan kualitas dan cerita pengrajin']),
                    $this->q('Customer Solo datang berpasangan dan ingin segera memutuskan. CS sebaiknya…', 'Segmen Solo membutuhkan respons teknis cepat dan visualisasi produk.', 0, ['Menjawab teknis dengan cepat sambil menunjukkan visual produk', 'Menunda jawaban hingga besok', 'Hanya memberi brosur', 'Memulai dengan negosiasi harga']),
                    $this->q('Bonus yang relevan untuk luxury customer Surabaya adalah…', 'Segmen Surabaya mengapresiasi bonus seperti laser nama atau kristal Swarovski.', 2, ['Voucher ongkir saja', 'Template chat', 'Laser nama atau Kristal Swarovski', 'Diskon tanpa penjelasan']),
                    $this->q('Saran gaya yang relevan untuk customer Bandung adalah…', 'Sebagai konsultan gaya, CS dapat mengaitkan finishing atau warna dengan preferensi customer.', 1, ['Menolak referensi foto', 'Menyarankan finishing dan warna sesuai preferensi', 'Memaksa paket paling mahal', 'Menghindari pembahasan desain']),
                ],
            ],
            4 => [
                'pre_test' => [
                    $this->q('Produk high-end dalam modul diposisikan sebagai…', 'Pendekatan modul mengubah peran CS menjadi konsultan aset emosional.', 1, ['Barang diskon massal', 'Aset emosional dan simbol komitmen', 'Komoditas untuk jual-beli cepat', 'Produk tanpa nilai layanan']),
                    $this->q('Safe haven dalam konteks materi digunakan untuk menjelaskan…', 'Materi menjelaskan kecenderungan harga material mulia yang dapat berubah seiring kondisi ekonomi.', 2, ['Harga pasti selalu turun', 'Cincin custom mudah dijual kembali', 'Menunda pembelian dapat membuat harga material lebih tinggi', 'Semua produk memiliki harga tetap']),
                    $this->q('Top-Down Anchoring dimulai dengan…', 'Presentasi dimulai dari Signature Package agar pilihan berikutnya terasa lebih terjangkau.', 0, ['Pilihan paket tertinggi', 'Pilihan termurah', 'Diskon acak', 'Biaya produksi saja']),
                    $this->q('High-Value Inquiry mengganti pertanyaan budget dengan…', 'Pertanyaan menggali nilai seperti ketahanan dan kenyamanan pemakaian.', 3, ['Nominal uang secara langsung', 'Jumlah diskon yang diinginkan', 'Nama kompetitor', 'Pentingnya bebas alergi dan tahan benturan']),
                    $this->q('Nilai utama cincin custom high-end menurut modul adalah…', 'Nilai emosional dan ketahanan lebih utama daripada likuiditas jual kembali.', 1, ['Keuntungan jual kembali cepat', 'Komitmen dan ketahanan jangka panjang', 'Harga termurah', 'Kemudahan diproduksi massal']),
                ],
                'post_test' => [
                    $this->q('Paradigma Dealmaker untuk produk di atas 20 juta adalah…', 'CS perlu menjadi konsultan aset emosional, bukan sekadar penjual barang.', 1, ['Penjual komoditas', 'Konsultan aset emosional', 'Pemberi diskon volume', 'Pengukur gramasi saja']),
                    $this->q('Argumen safe haven yang tepat dalam materi adalah…', 'Materi mengarahkan CS menjelaskan risiko penundaan ketika harga material cenderung berubah.', 1, ['Cincin custom selalu naik di pasar sekunder', 'Penundaan dapat membuat harga material lebih tinggi', 'Semua harga material tetap', 'Likuiditas sama dengan pasar uang']),
                    $this->q('Efek Veblen pada produk high-end ditunjukkan oleh…', 'Produk dapat dipersepsikan lebih eksklusif ketika harga tinggi.', 2, ['Permintaan naik karena diskon besar', 'Minat turun saat harga naik', 'Permintaan meningkat karena simbol status', 'Customer hanya membandingkan gramasi']),
                    $this->q('Rekomendasi alokasi anggaran pernikahan adalah…', 'Materi menyarankan menggeser sebagian anggaran habis pakai ke cincin yang dipakai jangka panjang.', 1, ['Mengurangi anggaran cincin', 'Menggeser sebagian pos habis pakai ke cincin', 'Memaksa menaikkan seluruh anggaran', 'Mengabaikan batas finansial']),
                    $this->q('Reframing yang tepat untuk kebijakan tidak bisa dijual kembali adalah…', 'CS perlu jujur sambil menekankan nilai emosional dan ketahanan.', 1, ['Menyembunyikan fakta', 'Menjelaskan nilai emosional tidak untuk diperjualbelikan', 'Menjanjikan buyback rahasia', 'Membahas penyusutan saja']),
                    $this->q('Tujuan Top-Down Anchoring dengan Signature Package adalah…', 'Harga awal yang tinggi memberi konteks sehingga paket premium terasa lebih terjangkau.', 3, ['Memaksa paket tertinggi terjual', 'Menutup paket murah', 'Menaikkan harga tanpa nilai', 'Membantu komparasi harga paket premium']),
                    $this->q('Fitur Selling the Unseen Whiterock Platinum 950 adalah…', 'Materi menekankan sifat hipoalergenik dan kepadatan logam.', 1, ['Sangat ringan dan garansi pendek', 'Hipoalergenik serta padat', 'Likuiditas jual kembali tinggi', 'Harga mengikuti kurs terendah']),
                    $this->q('Pertanyaan High-Value Inquiry yang tepat adalah…', 'Pertanyaan menggali kebutuhan jangka panjang, bukan sekadar angka budget.', 2, ['Berapa budget maksimal?', 'Model apa yang paling murah?', 'Seberapa penting bebas alergi dan tahan benturan?', 'Kapan cincin akan dijual kembali?']),
                    $this->q('Kalimat tentang presisi handmade digunakan pada tahap…', 'Dalam Luxury Script, urgensi proses handmade digunakan untuk closing.', 3, ['Qualification', 'Demonstration', 'Value Adding', 'Closing']),
                    $this->q('Cara mengomunikasikan nilai sosial produk high-end adalah…', 'Materi menyebut kontribusi terhadap Ziswaf dan pendidikan anak yatim.', 1, ['Biaya promosi digital', 'Kontribusi lebih besar pada Ziswaf dan pendidikan yatim', 'Alasan menaikkan harga', 'Pengembalian dana donasi']),
                ],
            ],
            5 => [
                'pre_test' => [
                    $this->q('Esensi konversi di industri perhiasan adalah menjual…', 'Materi menekankan kepercayaan dan momen, bukan sekadar barang.', 1, ['Harga termurah', 'Kepercayaan dan momen', 'Jumlah katalog', 'Kecepatan produksi saja']),
                    $this->q('Fokus utama saat menangani lead online adalah…', 'Lead online membutuhkan kecepatan dan visual yang personal.', 0, ['Kecepatan respons dan visual', 'Edukasi panjang tanpa tanya', 'Menunggu customer datang', 'Menawarkan diskon massal']),
                    $this->q('Lead offline terutama membutuhkan…', 'Customer di store perlu pengalaman, sentuhan, dan kenyamanan.', 2, ['Broadcast promosi', 'Template chat', 'Pengalaman dan sentuhan produk', 'Formulir tanpa konsultasi']),
                    $this->q('Tujuan mirroring adalah…', 'Menyamakan energi dan tempo membantu membangun kedekatan.', 3, ['Membuat customer lebih lama menunggu', 'Meniru semua kata customer', 'Menghindari pertanyaan', 'Membangun kedekatan lewat tempo yang sesuai']),
                    $this->q('Rumus A-C-A diawali dengan…', 'Acknowledge berarti mengakui dan memahami keberatan customer.', 1, ['Menawarkan diskon', 'Mengakui keberatan customer', 'Membandingkan kompetitor', 'Menutup percakapan']),
                ],
                'post_test' => [
                    $this->q('Esensi dasar konversi perhiasan adalah…', 'Kepercayaan dan momen adalah nilai utama dalam proses konversi.', 1, ['Visual mewah semata', 'Nilai emosional, momen, dan kepercayaan', 'Respons cepat saja', 'Promo harga saja']),
                    $this->q('Dampak respons online lebih dari lima menit menurut materi adalah…', 'Materi menyebut potensi konversi dapat turun drastis karena distraksi digital.', 0, ['Potensi konversi turun drastis', 'Customer pasti datang ke toko', 'Kualitas produk berubah', 'Basket size otomatis naik']),
                    $this->q('Saat lead online menanyakan harga, tindakan tepat adalah…', 'Harga diikuti pertanyaan terbuka menjaga percakapan dan menggali kebutuhan.', 2, ['Harga lalu foto katalog saja', 'Menolak memberi harga', 'Harga lalu pertanyaan kebutuhan', 'Diskon instan']),
                    $this->q('Visual storytelling yang tepat melalui chat adalah…', 'Video real-life memperlihatkan finishing dan kilau nyata.', 2, ['Foto katalog saja', 'Video bengkel tanpa produk', 'Video real-life di cahaya alami', 'Foto sertifikat saja']),
                    $this->q('Customer offline terburu-buru perlu dilayani dengan…', 'Observasi dan mirroring berarti menyesuaikan energi dan fokus pada poin utama.', 1, ['Edukasi panjang', 'Tempo sesuai dan langsung ke poin utama', 'Tempo diperlambat', 'Brosur tanpa konsultasi']),
                    $this->q('Nilai tambah yang wajib dibahas saat edukasi produk di store adalah…', 'Material dan after-sales service seperti garansi finishing serta resize membangun value.', 1, ['Lokasi parkir', 'Material dan layanan purnajual', 'Kapasitas produksi', 'Margin keuntungan']),
                    $this->q('Contoh Trial Closing berbasis waktu yang tepat adalah…', 'Trial closing mengaitkan kesiapan produksi dengan kebutuhan waktu customer.', 2, ['Pilih debit atau kredit?', 'Isi pesanan sekarang tanpa konteks', 'Jadwal produksi aman untuk acara bulan depan', 'Voucher reservasi']),
                    $this->q('Follow-up setelah kunjungan store tanpa closing dilakukan…', 'Materi menyarankan maksimal H+2 dengan informasi bernilai tambah.', 1, ['H+1 dengan diskon di luar kebijakan', 'Maksimal H+2 dengan informasi tambahan', 'Setelah satu minggu lalu hapus data', 'Broadcast setiap hari']),
                    $this->q('Tahap Confirm dalam A-C-A dicontohkan dengan…', 'Confirm memastikan keberatan utama customer sebelum menjawab.', 1, ['Saya paham harga penting', 'Selain harga, apakah model sudah sesuai?', 'Material ini tahan lama', 'Kompetitor memberi harga murah?']),
                    $this->q('Metrik keberhasilan dari total lead yang ditangani adalah…', 'Closing Rate mengukur persentase keberhasilan dari lead yang ditangani.', 2, ['Leads All ke Leads Serious', 'Basket Size', 'Closing Rate', 'Upselling Product Ratio']),
                ],
            ],
            7 => [
                'pre_test' => [
                    $this->q('Customer detail-oriented dan budget-conscious umumnya merasa…', 'Mereka detail karena ingin dana terbatas menghasilkan produk yang memuaskan.', 1, ['Tidak membutuhkan konsultasi', 'Takut kecewa atas hasil dari budget terbatas', 'Tidak pernah membandingkan toko', 'Selalu memilih material termahal']),
                    $this->q('Tujuan Consultative Filtering adalah…', 'CS menghargai detail desain sambil menggali budget untuk mencari racikan paling tepat.', 2, ['Menolak desain rumit', 'Menyetujui semua permintaan', 'Mencari solusi detail sesuai budget', 'Memotong harga tanpa tanya']),
                    $this->q('Material substitution membantu customer dengan…', 'Perbandingan visual membantu customer memahami alternatif yang tampilannya mirip tetapi harganya berbeda.', 0, ['Alternatif material dengan tampilan yang mendekati', 'Janji semua material sama awet', 'Diskon tanpa perubahan material', 'Revisi desain tanpa batas']),
                    $this->q('Mengapa revisi desain perlu dibatasi di awal?', 'Detail rumit pada material tertentu dapat memiliki risiko teknis.', 3, ['Agar customer tidak bertanya', 'Agar desain selalu polos', 'Agar produksi memakai mesin', 'Agar risiko teknis dapat dikelola']),
                    $this->q('Value Over Price mengarahkan CS membahas…', 'Manfaat jangka panjang seperti garansi dapat diterjemahkan sebagai biaya per tahun yang masuk akal.', 1, ['Harga termurah hari ini', 'Manfaat dan ketenangan jangka panjang', 'Kompetitor yang lebih mahal', 'Diskon tanpa syarat']),
                ],
                'post_test' => [
                    $this->q('Alasan customer menjadi sangat detail dan perfeksionis adalah…', 'Mereka ingin budget terbatas menghasilkan produk yang sempurna dan menghindari kekecewaan.', 1, ['Menjatuhkan kredibilitas toko', 'Takut kecewa atas hasil dari dana terbatas', 'Hanya mencari harga termurah', 'Menyamakan semua material']),
                    $this->q('Respons pertama saat customer online meminta detail rumit adalah…', 'Validasi preferensi customer lalu gali budget dengan sopan.', 2, ['Langsung menolak', 'Menyetujui lalu menaikkan harga', 'Memuji detail lalu menanyakan budget', 'Memaksa template standar']),
                    $this->q('Mengapa visualisasi penting bagi customer detail-oriented?', 'Foto atau video perbandingan memberi kepastian visual atas alternatif yang lebih terjangkau.', 2, ['Membuktikan semua logam sama', 'Memaksa budget naik', 'Menunjukkan alternatif bisa tampak mirip', 'Menghapus semua revisi']),
                    $this->q('Alasan teknis membatasi revisi pada material budget minim adalah…', 'Detail terlalu rumit dapat menimbulkan risiko teknis dan menurunkan kualitas.', 1, ['Silver selalu sangat lambat dibuat', 'Detail rumit dapat berisiko secara teknis', 'Toko rugi karena bahan habis', 'Pengrajin menolak Silver']),
                    $this->q('Price Anchoring & Breakdown dilakukan dengan…', 'Mulai dari material yang paling mendekati keinginan lalu tawarkan alternatif tanpa menghilangkan desain.', 2, ['Mulai dari material termurah', 'Memotong harga emas langsung', 'Tampilkan Emas lalu alternatif Palladium', 'Pisahkan biaya tanpa rekomendasi']),
                    $this->q('Edukasi technical constraints yang tepat untuk microsetting pada budget minim adalah…', 'CS dapat menawarkan material lebih solid atau menyederhanakan detail agar struktur tetap kuat.', 1, ['Mengganti model menjadi polos', 'Pilih material lebih solid atau sederhanakan detail', 'Menaikkan biaya dua kali lipat', 'Menyetujui tanpa perubahan']),
                    $this->q('Argumentasi Value Over Price yang efektif adalah…', 'Garansi resize dan poles memberi nilai jangka panjang atas selisih harga kecil.', 1, ['Harga mahal selalu bergengsi', 'Selisih kecil memberi garansi dan ketenangan jangka panjang', 'Harga mahal pasti investasi', 'Harga murah menghapus hak komplain']),
                    $this->q('Diskon tidak boleh diberikan cuma-cuma karena…', 'Customer detail dapat terus meminta bonus atau detail tambahan tanpa kompensasi.', 2, ['Produk tampak palsu', 'Margin pasti nol', 'Permintaan tambahan dapat terus meningkat', 'Customer pasti membatalkan']),
                    $this->q('Saat customer meminta harga turun di bawah standar, respons Give and Take adalah…', 'Kompensasi dapat berupa mengganti bonus eksklusif dengan bonus standar.', 1, ['Menolak dan menghentikan negosiasi', 'Ajukan harga dengan kompensasi bonus kotak standar', 'Turunkan harga diam-diam mengurangi produk', 'Diskon sekaligus tambah bonus']),
                    $this->q('Jika desain impian tidak sesuai budget, solusi profesional adalah…', 'Tawarkan substitusi material sambil mempertahankan estetika dan menjelaskan batas teknisnya.', 3, ['Memaksa customer menaikkan budget', 'Menolak semua detail', 'Menjanjikan hasil di luar kemampuan material', 'Merekomendasikan alternatif material dengan jujur']),
                ],
            ],
            8 => [
                'pre_test' => [
                    $this->q('Tujuan utama precision dalam pelayanan adalah…', 'Ketelitian mencegah kesalahan operasional dan komplain.', 0, ['Mencegah kesalahan sebelum produk diserahkan', 'Mempercepat refund', 'Mengurangi konsultasi', 'Menyalahkan produksi']),
                    $this->q('Metode HEART diawali dengan…', 'Hear berarti mendengarkan customer sampai tuntas.', 1, ['Memberi solusi', 'Mendengarkan customer', 'Menilai kesalahan', 'Menutup komplain']),
                    $this->q('The 5 Whys digunakan untuk…', 'Metode ini mencari akar masalah, bukan hanya gejalanya.', 2, ['Menentukan diskon', 'Memilih material', 'Mencari akar masalah', 'Memilih cabang']),
                    $this->q('Saat menghadapi komplain berat, CS perlu mengingat bahwa…', 'Customer marah pada situasi atau produk, bukan pribadi CS.', 3, ['Komplain adalah serangan pribadi', 'CS harus selalu berdebat', 'Refund selalu wajib', 'Marahnya customer bukan personal']),
                    $this->q('Under-promise, over-deliver berarti…', 'Ekspektasi perlu disampaikan realistis agar hasil baik dapat melampaui janji.', 1, ['Menjanjikan kemiripan 100 persen', 'Menyampaikan ekspektasi realistis', 'Menyembunyikan risiko', 'Tidak memberi informasi']),
                ],
                'post_test' => [
                    $this->q('Quality Control Mandiri yang utuh sebelum penyerahan produk adalah…', 'QC fisik mencakup finishing, mata batu, dan warna produk.', 1, ['Mengecek catatan form saja', 'Mengecek finishing, mata batu, dan warna', 'Menghitung waktu produksi saja', 'Menyerahkan tanpa cek']),
                    $this->q('Untuk desain handmade rumit, komunikasi ekspektasi yang tepat adalah…', 'Modul menyarankan menyampaikan kemiripan realistis 80–85 persen bila ada batas teknis.', 2, ['Menolak tanpa penjelasan', 'Menjanjikan kemiripan 100 persen', 'Menjelaskan kemiripan realistis dan batas teknis', 'Hanya menjelaskan garansi']),
                    $this->q('Edukasi karakteristik material perlu dilakukan sejak awal karena…', 'Komplain kerap muncul dari ekspektasi yang tidak dikelola.', 0, ['Ekspektasi yang tidak dikelola memicu komplain', 'Agar klaim garansi selalu ditolak', 'Agar form cepat selesai', 'Semua material cepat berubah warna']),
                    $this->q('Mendengarkan customer sampai tuntas bertujuan untuk…', 'Hear memberi perhatian penuh dan membantu customer merasa didengar.', 1, ['Mengulur waktu', 'Memberi perhatian penuh pada keluhan', 'Mencari kelemahan customer', 'Menyalahkan produksi']),
                    $this->q('Kalimat “Saya mengerti betapa pentingnya cincin ini…” menerapkan tahap…', 'Kalimat ini memvalidasi emosi dan pentingnya momen bagi customer.', 2, ['Apologize', 'Respond & Resolve', 'Empathize', 'Thank']),
                    $this->q('Permintaan maaf yang tepat disampaikan…', 'Permintaan maaf harus tulus tanpa mencari alasan atau menyalahkan pihak lain.', 1, ['Sambil menyalahkan produksi', 'Tanpa defensif atau menyalahkan pihak lain', 'Setelah menemukan siapa yang salah', 'Hanya jika refund disetujui']),
                    $this->q('Jika note form rancu menyebabkan hasil tidak sesuai, tindakan korektifnya adalah…', 'Standarisasi note mencegah masalah yang sama terulang.', 1, ['Memberi sanksi langsung', 'Menstandarkan note pada form', 'Meminta customer menulis sendiri', 'Mengganti semua handmade']),
                    $this->q('Tujuan memahami batasan wewenang adalah…', 'CS mengetahui keputusan mandiri dan kapan perlu eskalasi ke atasan.', 1, ['Membatasi kreativitas', 'Mengetahui batas keputusan dan eskalasi', 'Menjamin kompensasi gratis', 'Mempersingkat semua komplain']),
                    $this->q('Sikap tepat menghadapi customer perfectionist yang marah adalah…', 'CS perlu tetap tenang, tidak mengambil komplain secara personal, dan belajar dari kejadian.', 2, ['Membela diri keras', 'Menolak mendengar', 'Tidak personal dan tetap growth mindset', 'Langsung setuju refund']),
                    $this->q('Saat tidak ada kesalahan teknis namun customer meminta refund atau buat ulang, CS sebaiknya…', 'Tawarkan solusi win-win seperti modifikasi prioritas atau penyesuaian finishing.', 1, ['Menyalahkan customer', 'Meminta maaf dan menawarkan modifikasi atau finishing', 'Membuat ulang otomatis', 'Menggantung keputusan']),
                ],
            ],
            9 => [
                'pre_test' => [
                    $this->q('Tekanan target dan komplain dalam modul dipandang sebagai…', 'Tekanan dipandang sebagai proses pembentukan dan tanda kepercayaan pada kapasitas CS.', 2, ['Alasan berhenti bekerja', 'Bukti kegagalan pribadi', 'Kesempatan berkembang', 'Kesalahan customer']),
                    $this->q('Hal yang dapat dikontrol CS adalah…', 'CS dapat mengontrol follow-up, kualitas chat, dan keramahan.', 1, ['Ekonomi makro', 'Follow-up dan kualitas layanan', 'Respons semua customer', 'Harga kompetitor']),
                    $this->q('Teknik napas lima detik digunakan agar…', 'Jeda singkat menjaga respons tetap profesional dan tenang.', 3, ['Customer menunggu lebih lama', 'CS tidak perlu menjawab', 'Harga terlihat lebih tinggi', 'Respons tidak terbawa emosi']),
                    $this->q('Analisis 80/20 pada leads berarti…', 'Energi diarahkan pada lead paling potensial menghasilkan omzet.', 0, ['Fokus pada 20 persen lead paling potensial', 'Menghubungi semua lead sama banyak', 'Menghapus seluruh lead lama', 'Menurunkan target']),
                    $this->q('Micro-Wins membantu CS dengan…', 'Target besar dipecah menjadi target harian agar kepercayaan diri pulih.', 1, ['Menghapus target bulanan', 'Membangun percaya diri melalui target kecil', 'Menambah tekanan', 'Menghindari follow-up']),
                ],
                'post_test' => [
                    $this->q('Makna tepat tekanan target tinggi dan komplain bagi CS adalah…', 'Materi menyebut tekanan sebagai indikator kepercayaan perusahaan pada kapasitas CS.', 1, ['Risiko operasional semata', 'Tanda perusahaan percaya pada kapasitas CS', 'Ujian fisik', 'Cara menyaring CS lemah']),
                    $this->q('Contoh identifikasi kontrol saat performa turun adalah…', 'Fokus pada kualitas follow-up dan keramahan, bukan faktor luar.', 2, ['Menganalisis ekonomi makro', 'Mengubah perilaku semua customer', 'Fokus follow-up dan keramahan', 'Meminta target diturunkan']),
                    $this->q('Manfaat teknik napas lima detik sebelum membalas chat sulit adalah…', 'Jeda membantu CS memberi respons profesional dan tenang.', 0, ['Respons tetap profesional dan tenang', 'Mencari data teknis', 'Membuat customer takut', 'Menyusun penolakan']),
                    $this->q('Saat Closing Rate turun, langkah pertama yang tepat adalah…', 'CS perlu melakukan audit mandiri atas kualitas interaksi sebelum menyalahkan lead.', 2, ['Pindah platform langsung', 'Berhenti chat sementara', 'Audit mandiri kualitas chat', 'Menerapkan 80/20 secara acak']),
                    $this->q('Fokus analisis 80/20 pada database leads adalah…', '20 persen lead potensial diproyeksikan memberi 80 persen omzet.', 2, ['80 persen energi ke semua lead', '80 persen energi ke lead mudah dihubungi', 'Fokus pada 20 persen lead berpotensi tinggi', 'Mengabaikan semua lead lama']),
                    $this->q('Audit mandiri kualitas interaksi mencakup…', 'Materi menekankan ketulusan sapaan, solusi, dan ketepatan follow-up.', 1, ['Platform dan target omzet', 'Sapaan, solusi, dan ketepatan follow-up', 'Faktor eksternal dan target besar', 'Jumlah lead dan teknik napas']),
                    $this->q('Mengapa Micro-Wins penting?', 'Target harian realistis mengembalikan kepercayaan diri saat target besar terasa berat.', 1, ['Mengalihkan dari semua target', 'Target harian realistis memulihkan percaya diri', 'Membuktikan database selalu baik', 'Memberi bonus harian']),
                    $this->q('Makna “kulit badak tetapi hati emas” adalah…', 'CS tangguh menghadapi penolakan namun tetap empatik dan solutif.', 0, ['Tangguh namun tetap empatik dan solutif', 'Tegas mempertahankan harga', 'Mengejar keuntungan pribadi', 'Mengabaikan keraguan customer']),
                    $this->q('Narasi “pelanggan lain juga merasakan keraguan yang sama” berada pada tahap…', 'Felt menormalisasi keraguan dengan menunjukkan pengalaman orang lain.', 2, ['Feel', 'Found', 'Felt', 'Objection']),
                    $this->q('Menurut materi, penjualan banyak terjadi pada…', 'Materi menekankan follow-up yang konsisten sebagai bagian penting proses penjualan.', 2, ['Chat pertama saja', 'Satu teknik objection', 'Follow-up yang konsisten', 'Memanggil semua database lama']),
                ],
            ],
            10 => [
                'pre_test' => [
                    $this->q('Growth Mindset melihat penolakan sebagai…', 'Penolakan menjadi bahan belajar untuk memperbaiki pendekatan berikutnya.', 0, ['Kesempatan memperbaiki pendekatan', 'Bukti bakat tidak cukup', 'Alasan berhenti follow-up', 'Kesalahan customer']),
                    $this->q('Target bagi Growth Mindset adalah…', 'Target dipandang sebagai tantangan untuk berkembang.', 1, ['Ancaman pribadi', 'Tantangan untuk naik kelas', 'Alasan iri pada rekan', 'Formalitas tanpa makna']),
                    $this->q('Kritik manajer sebaiknya dipandang sebagai…', 'Feedback adalah bahan perbaikan, bukan serangan pribadi.', 2, ['Serangan pribadi', 'Alasan mencari pembelaan', 'Umpan balik untuk perbaikan', 'Bukti kegagalan']),
                    $this->q('Kompetisi sehat bertujuan untuk…', 'Kompetisi mendorong perbaikan diri dan standar layanan tanpa merusak tim.', 3, ['Menjatuhkan rekan', 'Menyembunyikan strategi', 'Menguasai database rekan', 'Mendorong standar layanan dan perbaikan diri']),
                    $this->q('Etika “The Kotagede Way” melarang…', 'CS harus menghormati antrean dan kepemilikan lead sesuai aturan.', 1, ['Berbagi strategi', 'Membajak lead rekan kerja', 'Merayakan kemenangan tim', 'Membantu junior']),
                ],
                'post_test' => [
                    $this->q('Setelah tiga penolakan, respons Growth Mindset yang tepat adalah…', 'CS mengevaluasi pendekatan agar interaksi berikutnya lebih sesuai kebutuhan prospek.', 1, ['Menyalahkan kualitas lead', 'Mencari hal yang dapat diperbaiki dari pendekatan', 'Menganggap target terlalu tinggi', 'Mengabaikan pelajaran dari penolakan']),
                    $this->q('Perbedaan Fixed dan Growth Mindset terhadap target adalah…', 'Fixed melihat target sebagai ancaman, Growth sebagai tantangan untuk naik kelas.', 1, ['Growth mencari pujian manajemen', 'Fixed melihat beban, Growth melihat tantangan', 'Growth tidak peduli angka', 'Target rekan adalah beban']),
                    $this->q('Respons Growth Mindset terhadap kritik leader adalah…', 'Kritik diterima sebagai feedback bernilai untuk perbaikan.', 2, ['Defensif', 'Menyalahkan faktor luar', 'Menggunakan feedback untuk perbaikan', 'Pasrah pada bakat']),
                    $this->q('Contoh Embrace Challenges adalah…', 'CS tidak memilih-milih lead dan belajar menangani customer sulit.', 3, ['Memilih lead yang ramah', 'Mengalihkan customer sulit', 'Menolak lead baru', 'Melayani lead tanpa pilih-pilih']),
                    $this->q('Saat Closing Rate turun, Persistence diterapkan dengan…', 'CS menganalisis Leads Serious dan menambah follow-up yang relevan.', 2, ['Mengabaikan angka', 'Berhenti follow-up', 'Analisis LS lalu follow-up ekstra', 'Meminta semua data diganti']),
                    $this->q('Learning over Validation berarti…', 'Fokusnya menjadi ahli produk dan layanan, bukan sekadar mengejar pujian.', 1, ['Mengejar pujian manajemen', 'Belajar bahan, desain, dan investasi produk', 'Mempercepat transaksi tanpa edukasi', 'Meniru tanpa memahami']),
                    $this->q('Esensi kompetisi omzet yang sehat adalah…', 'Kompetisi mendorong self-improvement, standar layanan, dan energi positif.', 2, ['Menyaring CS tidak produktif', 'Membuktikan siapa paling unggul', 'Mendorong perbaikan dan energi positif', 'Membuat junior mandiri']),
                    $this->q('Contoh tindakan membajak lead adalah…', 'Mengambil alih komunikasi tanpa menghormati antrean atau kepemilikan database melanggar etika.', 0, ['Mengambil lead tanpa menghormati antrean', 'Membantu rekan dengan izin', 'Mengolah data lama sesuai aturan', 'Mempelajari strategi rekan']),
                    $this->q('Senior CS yang menemukan cara meningkatkan Basket Size sebaiknya…', 'Transparansi strategi mendorong senior berbagi kepada junior.', 1, ['Menyimpan trik sendiri', 'Berbagi formula kepada junior', 'Hanya berbagi ke senior', 'Menjual trik di luar kerja']),
                    $this->q('Collaborative Competition berjalan dengan…', 'Skor individu tetap dihitung dan target tim memberi apresiasi bersama.', 2, ['Menghapus seluruh skor individu', 'Target tim hanya formalitas', 'Skor individu dan target tim bersama', 'Mengeliminasi yang tertinggal']),
                ],
            ],
        ];
    }

    /** @param array<int, string> $answers
     *  @return array{prompt: string, explanation: string, correct: int, answers: array<int, string>} */
    private function q(string $prompt, string $explanation, int $correct, array $answers): array
    {
        return compact('prompt', 'explanation', 'correct', 'answers');
    }

    /** @return array<int, array{title: string, presentation_url: string, video_url: string}> */
    private function modules(): array
    {
        return [
            ['title' => 'CS Dealmaker Mastery', 'presentation_url' => 'https://www.canva.com/design/DAHJVwit-10/Z7OevY3UT_3y3nbIKMstkw/view', 'video_url' => 'https://youtu.be/DphVgtOniXc'],
            ['title' => 'Product Knowledge', 'presentation_url' => 'https://www.canva.com/design/DAHJbhbdWnk/xmzjGgpsZE98qm7zYwKcLA/view', 'video_url' => 'https://youtu.be/nf8GqkgCKlk'],
            ['title' => 'Work Environment & Customer Character Analysis', 'presentation_url' => 'https://www.canva.com/design/DAHJss7IOao/y2hsFGtxoh80O0MognzZ2Q/view', 'video_url' => 'https://youtu.be/W7hghh3cunI'],
            ['title' => 'High-End Product Sales Strategy', 'presentation_url' => 'https://www.canva.com/design/DAHJuExbLC4/--KSSFG9JHoifWumQMosUg/view', 'video_url' => 'https://youtu.be/Bb3olSJnsP0'],
            ['title' => 'High Convertion Strategy', 'presentation_url' => 'https://www.canva.com/design/DAHJ_JGW1F4/atk4tzQ1hxGlCB2g_PLjnA/view', 'video_url' => 'https://youtu.be/NrBOCXLlBXE'],
            ['title' => 'Customer Service Career Path', 'presentation_url' => 'https://canva.link/89g2ztdcfb7og9q', 'video_url' => 'https://youtu.be/mN7Zb-KDSKE'],
            ['title' => 'Mastering Detailed Oriented & Budget Conscious', 'presentation_url' => 'https://canva.link/wqzlyop3l4h4dfx', 'video_url' => 'https://youtu.be/vvE0wckeMxQ'],
            ['title' => 'Excellent Problem Solving & ComPlaint Handling', 'presentation_url' => 'https://canva.link/l4k0jufis0p773v', 'video_url' => 'https://youtu.be/i9g6XEZUFLc'],
            ['title' => 'The Jewellery Warior', 'presentation_url' => 'https://canva.link/ipamd5sqj5naqt0', 'video_url' => 'https://youtu.be/Rz9Es3qUU6k'],
            ['title' => 'Sales Excellent & Winning Spirit', 'presentation_url' => 'https://canva.link/zfxyuysode30fp3', 'video_url' => 'https://youtu.be/k1FO1xfwqHI'],
        ];
    }
}
