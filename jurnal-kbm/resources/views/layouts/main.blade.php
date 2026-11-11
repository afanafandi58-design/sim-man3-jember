<!DOCTYPE html>
<html lang="id">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SIM MAN 3 Jember</title>

    @vite(['resources/css/app.css','resources/js/app.js'])

</head>

<body class="bg-gray-100">

<nav class="bg-green-700 text-white shadow">

    <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between">

        <div>

            <h1 class="text-2xl font-bold">

                SIM MAN 3 JEMBER

            </h1>

            <small>

                Sistem Informasi Manajemen Madrasah

            </small>

        </div>

        <div>

            Admin

        </div>

    </div>

</nav>


<div class="bg-white shadow">

    <div class="max-w-7xl mx-auto px-6 py-3">

        <a href="/dashboard">Dashboard</a>

        |

        <a href="/guru">Guru</a>

        |

        <a href="/jadwal">Jadwal</a>

        |

        <a href="#">Jurnal KBM</a>

    </div>

</div>


<div class="max-w-7xl mx-auto mt-6">

    @yield('content')

</div>

</body>

</html>