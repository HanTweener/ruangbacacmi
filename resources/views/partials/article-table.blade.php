@if ($articles->isEmpty())
    <div class="empty-state">
        <span aria-hidden="true">
            ✎
        </span>
        <h3>Belum ada artikel.</h3>
        <p>Buat artikel lalu simpan sebagai draft atau publikasikan.</p>
        <a class="btn" href="{{ route('articles.create') }}">
            Tambah artikel
        </a>
    </div>
@else
    <div
        class="table-scroll"
        tabindex="0"
        role="region"
        aria-label="Daftar artikel Anda"
    >
        <table>
            <thead>
                <tr>
                    <th scope="col">Artikel</th>
                    <th scope="col">Status</th>
                    <th scope="col">Dibuat</th>
                    <th scope="col">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($articles as $article)
                    <tr>
                        <td>
                            <a class="table-title" href="{{ route('articles.show', $article) }}">
                                {{ $article->judul }}
                            </a>
                            <small>
                                {{ $article->kategori }}
                            </small>
                        </td>
                        <td>
                            <span class="badge {{ $article->status ? 'published' : 'draft' }}">
                                {{ $article->status ? 'Published' : 'Draft' }}
                            </span>
                        </td>
                        <td class="nowrap">
                            {{ $article->created_at->translatedFormat('d M Y') }}
                        </td>
                        <td>
                            <div class="table-actions">
                                <a href="{{ route('articles.edit', $article) }}"
                                    aria-label="Edit {{ $article->judul }}">
                                    Edit
                                </a>
                                <form
                                    method="post"
                                    action="{{ route('articles.destroy', $article) }}"
                                    data-delete
                                >
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        class="text-danger"
                                        type="submit"
                                        aria-label="Hapus {{ $article->judul }}"
                                    >
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
