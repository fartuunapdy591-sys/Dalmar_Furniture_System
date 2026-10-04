@extends('layouts.app')

@section('title', 'Messages')

@section('content')
    <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
        <div>
            <div class="page-title">Contact Messages</div>
            <div class="page-subtitle">Home / Messages</div>
        </div>
    </div>

    <div class="card-panel">
        <table class="table-dalmar">
            <thead>
            <tr>
                <th>From</th>
                <th>Subject</th>
                <th>Phone</th>
                <th>Received</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            @forelse($messages as $message)
                <tr class="{{ $message->is_read ? '' : 'fw-semibold' }}">
                    <td>{{ $message->name }}</td>
                    <td>{{ $message->subject ?: '-' }}</td>
                    <td>{{ $message->phone ?: '-' }}</td>
                    <td>{{ $message->created_at->format('M d, Y H:i') }}</td>
                    <td>
                        @if($message->is_read)
                            <span class="badge-method">Read</span>
                        @else
                            <span class="badge-method" style="background:#fdecea; color:#c0392b;">New</span>
                        @endif
                    </td>
                    <td>
                        <button type="button" class="icon-btn" data-bs-toggle="modal" data-bs-target="#viewMessageModal{{ $message->id }}">
                            <i class="bi bi-eye-fill"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No messages yet.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div class="mt-3">{{ $messages->links() }}</div>
    </div>

    @foreach($messages as $message)
        <div class="modal fade" id="viewMessageModal{{ $message->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $message->subject ?: 'Message from '.$message->name }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="small text-muted">Name</div>
                                <div class="fw-semibold">{{ $message->name }}</div>
                            </div>
                            <div class="col-6">
                                <div class="small text-muted">Phone</div>
                                <div class="fw-semibold">{{ $message->phone ?: '-' }}</div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="small text-muted">Email</div>
                                <div class="fw-semibold">{{ $message->email ?: '-' }}</div>
                            </div>
                            <div class="col-6 mt-2">
                                <div class="small text-muted">Received</div>
                                <div class="fw-semibold">{{ $message->created_at->format('M d, Y H:i') }}</div>
                            </div>
                        </div>
                        <div class="small text-muted mb-1">Message</div>
                        <p class="mb-0">{{ $message->message }}</p>
                    </div>
                    <div class="modal-footer flex-wrap">
                        @php
                            $waNumber = ltrim(preg_replace('/\D+/', '', (string) $message->phone), '0');
                            $replySubject = rawurlencode('Re: '.($message->subject ?: 'Your message to Dalmar Furniture'));
                        @endphp
                        @if($waNumber)
                            <a href="https://wa.me/{{ $waNumber }}?text={{ rawurlencode('Salaan '.$message->name.', ') }}" target="_blank" rel="noopener" class="btn btn-success">
                                <i class="bi bi-whatsapp me-1"></i> Reply WhatsApp
                            </a>
                            <a href="tel:{{ $message->phone }}" class="btn btn-light"><i class="bi bi-telephone-fill me-1"></i> Call</a>
                        @endif
                        @if($message->email)
                            <a href="mailto:{{ $message->email }}?subject={{ $replySubject }}" class="btn btn-navy">
                                <i class="bi bi-envelope-fill me-1"></i> Reply Email
                            </a>
                        @endif
                        @if(! $message->is_read)
                            <form action="{{ route('messages.read', $message) }}" method="POST">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn btn-light">Mark as Read</button>
                            </form>
                        @endif
                        <form action="{{ route('messages.destroy', $message) }}" method="POST" onsubmit="return confirm('Delete this message?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger">Delete</button>
                        </form>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
@endsection
