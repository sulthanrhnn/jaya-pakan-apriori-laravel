@if(session('success'))
    <div class="alert alert-success" data-alert>
        <span>{{ session('success') }}</span><button type="button" class="alert-close" aria-label="Tutup">×</button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger" data-alert>
        <span>{{ session('error') }}</span><button type="button" class="alert-close" aria-label="Tutup">×</button>
    </div>
@endif
@if($errors->any())
    <div class="alert alert-danger" data-alert>
        <div><strong>Periksa kembali data berikut:</strong><ul class="compact-list">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        <button type="button" class="alert-close" aria-label="Tutup">×</button>
    </div>
@endif
