@extends('lms.page')

@section('title', 'Monitoring LMS')

@section('content_header')<h1>Monitoring LMS per Guru</h1>@stop

@section('content')
    <div class="card">
        <table class="table table-bordered mb-0">
            <thead><tr><th>Guru</th><th>Materi</th><th>Tugas</th><th>Kuis</th><th>Pengumpulan</th></tr></thead>
            <tbody>
                @foreach ($byGuru as $row)
                    <tr>
                        <td>{{ e($row['guru']->displayName()) }}</td>
                        <td>{{ $row['materials'] }}</td>
                        <td>{{ $row['assignments'] }}</td>
                        <td>{{ $row['quizzes'] }}</td>
                        <td>{{ $row['submissions'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@stop
