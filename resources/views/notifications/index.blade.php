<x-layouts.app title="Notifikasi">

<div class="flex items-center justify-between mb-5 flex-wrap gap-3">
    <div>
        <h2 class="text-lg font-bold text-slate-900 m-0 mb-0.5">Notifikasi</h2>
        <p class="text-xs text-slate-400 m-0">Riwayat notifikasi untuk akun Anda</p>
    </div>
    @if($notifications->getCollection()->contains(fn($n) => is_null($n->read_at)))
    <form method="POST" action="{{ route('notifications.read-all') }}">
        @csrf
        <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold bg-slate-100 text-slate-600 border-0 cursor-pointer hover:bg-slate-200 transition-colors">
            Tandai semua sudah dibaca
        </button>
    </form>
    @endif
</div>

@if($notifications->isEmpty())
<div class="bg-white rounded-xl shadow-sm py-16 px-5 text-center">
    <div class="w-16 h-16 rounded-2xl bg-slate-50 border border-slate-100 flex items-center justify-center mx-auto mb-4">
        <svg width="28" height="28" fill="none" stroke="#94a3b8" stroke-width="1.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
    </div>
    <div class="text-sm font-semibold text-slate-700 mb-1">Belum ada notifikasi</div>
    <div class="text-xs text-slate-400">Notifikasi tentang pengajuan dana akan muncul di sini.</div>
</div>
@else

<div class="flex flex-col gap-2.5">
    @foreach($notifications as $n)
    @php
        $isUnread = is_null($n->read_at);
        $isApproval = ($n->data['type'] ?? null) === 'fund_request_needs_approval';
        $status = $n->data['status'] ?? null;
    @endphp
    <form method="POST" action="{{ route('notifications.read', $n->id) }}" class="notif-form">
        @csrf
        <button type="submit" class="w-full text-left bg-white rounded-xl shadow-sm px-4 py-3.5 flex items-start gap-3 border-0 cursor-pointer hover:bg-slate-50 transition-colors {{ $isUnread ? 'ring-1 ring-orange-100' : '' }}">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5
                {{ $isApproval ? 'bg-orange-50' : ($status === 'approved' ? 'bg-green-50' : 'bg-red-50') }}">
                @if($isApproval)
                <svg width="16" height="16" fill="none" stroke="#ea580c" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                @elseif($status === 'approved')
                <svg width="16" height="16" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                @else
                <svg width="16" height="16" fill="none" stroke="#dc2626" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-mono text-xs font-bold text-slate-500">{{ $n->data['reference'] ?? '' }}</span>
                    @if($isUnread)
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                    @endif
                </div>
                <div class="text-sm font-semibold text-slate-800 mt-0.5">
                    @if($isApproval)
                        Menunggu persetujuan: {{ $n->data['title'] ?? '' }}
                    @elseif($status === 'approved')
                        Pengajuan disetujui: {{ $n->data['title'] ?? '' }}
                    @else
                        Pengajuan ditolak: {{ $n->data['title'] ?? '' }}
                    @endif
                </div>
                <div class="text-[11px] text-slate-400 mt-1">{{ $n->created_at->diffForHumans() }}</div>
            </div>
        </button>
    </form>
    @endforeach
</div>

{{-- Pagination --}}
@if($notifications->hasPages())
<div class="mt-4 flex justify-end gap-1">
    @if($notifications->onFirstPage())
        <span class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-300 pointer-events-none">&laquo;</span>
    @else
        <a href="{{ $notifications->previousPageUrl() }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 no-underline bg-white">&laquo;</a>
    @endif
    @foreach($notifications->getUrlRange(max(1,$notifications->currentPage()-2), min($notifications->lastPage(),$notifications->currentPage()+2)) as $page => $url)
        <a href="{{ $url }}" class="inline-flex items-center px-2.5 py-1.5 border rounded-lg text-xs no-underline {{ $page == $notifications->currentPage() ? 'bg-orange-500 border-orange-500 text-white' : 'bg-white border-slate-200 text-slate-500' }}">{{ $page }}</a>
    @endforeach
    @if($notifications->hasMorePages())
        <a href="{{ $notifications->nextPageUrl() }}" class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-500 no-underline bg-white">&raquo;</a>
    @else
        <span class="inline-flex items-center px-2.5 py-1.5 border border-slate-200 rounded-lg text-xs text-slate-300 pointer-events-none">&raquo;</span>
    @endif
</div>
@endif
@endif

<script>
document.querySelectorAll('.notif-form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': form.querySelector('input[name=_token]').value, 'Accept': 'application/json' },
        }).then(r => r.json()).then(data => {
            if (data.url && data.url !== '#') window.location.href = data.url;
            else window.location.reload();
        });
    });
});
</script>

</x-layouts.app>
