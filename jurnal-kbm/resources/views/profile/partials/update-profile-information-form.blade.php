<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    <div class="form-group">
        <label for="name">Nama</label>
        <input id="name" name="name" type="text" class="form-control"
               value="{{ old('name', $user->name) }}" required autocomplete="name">
        @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <div class="form-group">
        <label for="email">Email</label>
        <input id="email" name="email" type="email" class="form-control"
               value="{{ old('email', $user->email) }}" required autocomplete="username">
        @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
    </div>

    <button type="submit" class="btn btn-primary">Simpan Profil</button>
</form>
