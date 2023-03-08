## {{ \Illuminate\Support\Str::before($command->description, '.') }}

<a name="{{ str_replace(':', '-', $command->name) }}" style="display: inline-block; position: absolute; margin-top: -5rem;"></a>

{{ $command->description }}

```bash
// torchlight! {"lineNumbers": false}
php hyde {!! $command->usage[0] !!}
```

@if($command->definition->options)
**Supports the following options:**

@foreach ($command->definition->options as $data)
    {!!$controller->formatOption((array) $data, $command)!!}
@endforeach
@endif