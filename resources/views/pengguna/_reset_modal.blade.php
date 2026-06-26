<form method="POST" action="{{ route('pengguna.reset-password', $pengguna) }}"
      data-modal-form
      data-confirm="Reset kata sandi untuk {{ $pengguna->nama }}?"
      data-success="Kata sandi berhasil direset."
      x-data="{ show: false }">
    @csrf
    @method('PATCH')

    <div class="space-y-4 px-6 py-5">
        <div class="flex items-start gap-3 rounded-xl bg-amber-50 px-4 py-3">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-600">
                @svg('heroicon-o-key', 'h-5 w-5')
            </span>
            <p class="text-sm text-slate-600">Atur kata sandi baru untuk <span class="font-medium text-foreground">{{ $pengguna->nama }}</span> ({{ '@'.$pengguna->username }}).</p>
        </div>

        <div>
            <label for="reset_password" class="block text-sm font-medium text-slate-700">Kata Sandi Baru <span class="text-red-500">*</span></label>
            <div class="relative mt-1.5">
                <input id="reset_password" name="password" :type="show ? 'text' : 'password'" required minlength="6"
                    class="input pr-11" placeholder="Minimal 6 karakter" autocomplete="new-password">
                <button type="button" @click="show = !show"
                    class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-lg text-slate-400 transition hover:text-slate-600">
                    @svg('heroicon-o-eye', 'h-5 w-5', ['x-show' => '!show'])
                    @svg('heroicon-o-eye-slash', 'h-5 w-5', ['x-show' => 'show', 'x-cloak' => true])
                </button>
            </div>
        </div>

        <div>
            <label for="reset_password_confirmation" class="block text-sm font-medium text-slate-700">Konfirmasi Kata Sandi <span class="text-red-500">*</span></label>
            <input type="password" id="reset_password_confirmation" name="password_confirmation" required minlength="6"
                class="input mt-1.5" placeholder="Ulangi kata sandi" autocomplete="new-password">
        </div>
    </div>

    <div class="flex items-center justify-end gap-3 rounded-b-2xl border-t border-slate-100 bg-slate-50 px-6 py-4">
        <x-button variant="outline" size="sm" data-modal-close>Batal</x-button>
        <x-button variant="primary" size="sm" type="submit">Simpan Kata Sandi</x-button>
    </div>
</form>
