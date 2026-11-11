<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="form-group">
        <label for="update_password_current_password">Password Saat Ini</label>
        <input id="update_password_current_password" name="current_password" type="password"
               class="form-control" autocomplete="current-password">
        @error('current_password', 'updatePassword')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="update_password_password">Password Baru</label>
        <input id="update_password_password" name="password" type="password"
               class="form-control" autocomplete="new-password">
        @error('password', 'updatePassword')
            <div class="text-danger small">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="update_password_password_confirmation">Konfirmasi Password Baru</label>
        <input id="update_password_password_confirmation" name="password_confirmation" type="password"
               class="form-control" autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-warning">Ubah Password</button>
</form>
