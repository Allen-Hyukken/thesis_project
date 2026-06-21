<div>
    <h3 class="mb-1">{{ $class->class_name }}</h3>
    <span class="text-muted" style="font-size:13px;">
        {{ $class->subject }} • {{ $class->teacher->full_name ?? 'Unknown teacher' }}
    </span>
</div>
