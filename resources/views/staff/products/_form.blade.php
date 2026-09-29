@php($editing = isset($product) && $product->exists)
<div class="form-grid two">
    <div class="field"><label>Kode Produk <span>*</span></label><input name="code" value="{{ old('code', $product->code ?? '') }}" required maxlength="20" {{ $editing ? 'readonly' : '' }}><small>{{ $editing ? 'Kode dikunci karena menjadi identitas pada data transaksi dan Apriori.' : 'Contoh: P16. Gunakan huruf/angka tanpa spasi.' }}</small></div>
    <div class="field"><label>Nama Produk <span>*</span></label><input name="name" value="{{ old('name', $product->name ?? '') }}" required maxlength="180"></div>
    <div class="field"><label>Harga Satuan (Rp) <span>*</span></label><input type="number" name="price" min="0" step="1" value="{{ old('price', $product->price ?? '') }}" required></div>
    <div class="field"><label>Stok <span>*</span></label><input type="number" name="stock" min="0" step="1" value="{{ old('stock', $product->stock ?? 0) }}" required></div>
</div>
<div class="field"><label>Deskripsi</label><textarea name="description" rows="4" maxlength="2000">{{ old('description', $product->description ?? '') }}</textarea></div>
<div class="field"><label>Gambar Produk</label><input type="file" name="image" accept="image/jpeg,image/png,image/webp"><small>Opsional. JPG/PNG/WEBP maksimal 3 MB.</small></div>
<label class="check-row"><input type="checkbox" name="is_active" value="1" {{ old('is_active', $editing ? $product->is_active : true) ? 'checked' : '' }}><span>Tampilkan produk di katalog publik</span></label>
<div class="form-actions"><a class="btn btn-ghost" href="{{ route('staff.products.index') }}">Batal</a><button class="btn btn-primary">{{ $editing ? 'Simpan Perubahan' : 'Tambah Produk' }}</button></div>
