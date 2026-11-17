/** Konten diambil dari situs publik man3jember.sch.id — hanya presentasi yang dimodernisasi. */

export const site = {
  brand: 'MAN 3 JEMBER',
  taglineShort: 'CERDAS KREATIF',
  taglineCivitas: 'CIVITAS MAN 3 JEMBER KOMPAK SALING PERCAYA',
  motto:
    'Membiasakan civitas akademika berperilaku sesuai ajaran islam dan norma yang berlaku di masyarakat',
  email: 'humasmantiger@gmail.com',
  liveUrl: 'https://man3jember.sch.id/',
  social: {
    facebook: 'https://www.facebook.com/share/9T59pSFrFvKQNNjS/?mibextid=qi2Omg',
    instagram: 'https://www.instagram.com/mantiger.official?igsh=MW56eGYxMW12ZGZhbQ==',
    youtube: 'https://www.youtube.com/@man3jember510',
    twitter: 'https://twitter.com',
  },
}

export const nav = [
  { label: 'Beranda', href: '#beranda' },
  { label: 'Visi Misi', href: '#visi-misi' },
  {
    label: 'Info Madrasah',
    children: [
      { label: 'Pengumuman', href: 'https://man3jember.sch.id/pengumuman/' },
      { label: 'Agenda', href: 'https://man3jember.sch.id/agenda' },
      { label: 'Fasilitas', href: 'https://man3jember.sch.id/fasilitas/' },
    ],
  },
  {
    label: 'Pendidik dan Kependidikan',
    children: [
      { label: 'Daftar Guru', href: 'https://man3jember.sch.id/gtk' },
      { label: 'Staff', href: 'https://man3jember.sch.id/Staff' },
      { label: 'Ekstra Kurikuler', href: 'https://man3jember.sch.id/ekskul' },
    ],
  },
  {
    label: 'Gallery',
    children: [
      { label: 'Foto', href: 'https://man3jember.sch.id/galeri' },
      { label: 'Video', href: 'https://man3jember.sch.id/video' },
    ],
  },
  { label: 'Berita', href: '#berita' },
  { label: 'Pengumuman', href: 'https://man3jember.sch.id/pengumuman/' },
  { label: 'Download', href: 'https://man3jember.sch.id/download/' },
  { label: 'News Digital', href: 'https://man3jember.sch.id/pengumuman/hightlight-mei/' },
  { label: 'PRODISTIK', href: 'https://prodistik.man3jember.sch.id/' },
]

export const kepalaMadrasah = {
  name: 'IMAM SYAHRONI',
  title: 'Kepala Madrasah',
  date: 'April 1, 2026',
  photo: '/images/kamad.png',
  excerpt:
    "Assalamu'alaikum warahmatullahi wabarakatuh. Alhamdulillahirabbil 'alamin, puji syukur kita panjatkan ke hadirat Allah SWT atas limpahan rahmat dan karunia-Nya, sehingga kita dapat terus melangkah bersama dalam memajukan pendidikan di Madrasah yang..",
  link: 'https://man3jember.sch.id/editorial/sambutan-kepala-madrasah/',
}

export const quotes = [
  {
    text: 'Madrasah bukan hanya tempat mencari ilmu, tetapi tempat membentuk hati, adab, dan masa depan...',
    author: 'Abah',
  },
  {
    text: 'Prinsip ekonomi Islam yang adil dan transparan sejalan dengan nilai-nilai kejujuran dan integritas yang kita pelajari di madrasah.',
    author: 'SM',
  },
  {
    text: 'Tersiarlah cahaya ilmu dari siswa-siswi madrasah, Berakhlak, santun, dan beradab, tatkala mereka berjalan. Mereka adalah bunga-bunga yang harum di taman ilmu, Menghiasi dunia dengan kebaikan.....',
    author: "Biet's Hayyuni",
  },
  {
    text: 'Hidup bersosial yang baik mengajarkan kita untuk menghargai perbedaan dan saling mendukung dalam kebaikan....',
    author: 'Wilda Naela',
  },
  {
    text: 'Kedisiplinan adalah kunci kesuksesan; sikap yang baik adalah jalan menuju kebahagiaan abadi....',
    author: 'Usman Affan Ridoi',
  },
]

export const berita = [
  {
    date: '20 October 2026',
    title:
      'MAN 3 JEMBER MENGETUK PINTU LANGIT: IKHTIAR SPIRITUAL MEMOHON RAHMAT DAN TURUNNYA HUJAN',
    excerpt:
      'Jember, Selasa, 20 Oktober 2026 — Dalam balutan keimanan dan penuh kerendahan hati, seluruh civitas akademika MAN 3 Jember melaksanakan salat Istisqa sebagai ikhtiar spiritual untuk memohon rahmat dan turunnya..',
    image: '/images/berita-istisqa.jpg',
    link: 'https://man3jember.sch.id/man-3-jember-mengetuk-pintu-langit-ikhtiar-spiritual-memohon-rahmat-dan-turunnya-hujan/',
  },
  {
    date: '12 October 2026',
    title:
      'Cegah ISPA Sejak Dini, Puskesmas Jombang Edukasi Warga MAN 3 Jember tentang Pentingnya Hidup Sehat',
    excerpt:
      'JEMBER – Puskesmas Jombang menggelar sosialisasi pencegahan Infeksi Saluran Pernapasan Akut (ISPA) di MAN 3 Jember, Senin (12/10/2026). Kegiatan ini menyasar siswa dan guru sebagai bagian dari upaya meningkatkan kesadaran..',
    image: '/images/berita-ispa.png',
    link: 'https://man3jember.sch.id/cegah-ispa-sejak-dini-puskesmas-jombang-edukasi-warga-man-3-jember-tentang-pentingnya-hidup-sehat/',
  },
  {
    date: '12 October 2026',
    title:
      'Tiga Paslon Adu Gagasan, Warga MAN 3 Jember Tentukan Ketua dan Wakil Ketua OSIM 2026/2027',
    excerpt:
      'JEMBER – MAN 3 Jember menggelar debat kandidat calon Ketua dan Wakil Ketua Organisasi Siswa Intra Madrasah (OSIM) periode 2026/2027, yang dilanjutkan dengan pemilihan oleh seluruh warga madrasah. Kegiatan ini..',
    image: '/images/berita-debat.png',
    link: 'https://man3jember.sch.id/tiga-paslon-adu-gagasan-warga-man-3-jember-tentukan-ketua-dan-wakil-ketua-osim-2026-2027/',
  },
  {
    date: '27 August 2026',
    title:
      'MAN 3 Jember Gelar Pertemuan Wali Murid Kelas XII, Sosialisasikan Persiapan TKA 2026 dan Job Career Menuju Job Fair 2027',
    excerpt:
      'MAN 3 Jember menggelar pertemuan wali murid kelas XII Tahun Pelajaran 2026/2027 yang bertempat di aula madrasah pada Kamis (27/8). Kegiatan ini bertujuan untuk memperkuat sinergi antara madrasah dan orang..',
    image: '/images/berita-expo.jpg',
    link: 'https://man3jember.sch.id/man-3-jember-gelar-pertemuan-wali-murid-kelas-xii-sosialisasikan-persiapan-tka-2026-dan-job-career-menuju-job-fair-2027/',
  },
  {
    date: '2026',
    title: 'Bangga! Siswi MAN 3 Jember Terpilih sebagai Paskibraka Kabupaten Jember 2026',
    excerpt:
      'MAN 3 Jember — Menjelang peringatan Hari Ulang Tahun (HUT) ke-81 Kemerdekaan Republik Indonesia, kabar membanggakan kembali hadir dari MAN 3 Jember. Prestasi tersebut datang dari Amyra Nadia Safa, siswi..',
    image: '/images/berita-expo.jpg',
    link: 'https://man3jember.sch.id/category/berita/',
  },
]

export const newsDigital = [
  { label: 'News Digital at Juli', published: 'July 23, 2024', href: 'https://man3jember.sch.id/pengumuman/hightlight-mei/' },
  { label: 'News Digital at Juni', published: 'July 23, 2024', href: 'https://man3jember.sch.id/pengumuman/hightlight-mei/' },
  { label: 'Highlight Mei', published: 'May 6, 2026', href: 'https://man3jember.sch.id/pengumuman/hightlight-mei/' },
]

export const visiMisi = {
  title: 'VISI MISI MAN 3 JEMBER',
  link: 'https://man3jember.sch.id/visi-misi-man-3-jember/',
  summary:
    'Membiasakan civitas akademika berperilaku sesuai ajaran islam dan norma yang berlaku di masyarakat — mewujudkan generasi cerdas, kreatif, beriman, dan saling percaya.',
}

export const quickLinks = [
  { label: 'Pengumuman', href: 'https://man3jember.sch.id/pengumuman/' },
  { label: 'Agenda', href: 'https://man3jember.sch.id/agenda' },
  { label: 'Fasilitas', href: 'https://man3jember.sch.id/fasilitas/' },
  { label: 'Daftar Guru', href: 'https://man3jember.sch.id/gtk' },
  { label: 'Staff', href: 'https://man3jember.sch.id/Staff' },
  { label: 'Ekstra Kurikuler', href: 'https://man3jember.sch.id/ekskul' },
  { label: 'Galeri Foto', href: 'https://man3jember.sch.id/galeri' },
  { label: 'Video', href: 'https://man3jember.sch.id/video' },
  { label: 'Download', href: 'https://man3jember.sch.id/download/' },
  { label: 'PRODISTIK', href: 'https://prodistik.man3jember.sch.id/' },
]
