<div class="editor-js-block">
  <div class="container container--md">
    {!! ($style == 'unordered') ? '<ul>' : '<ol>' !!}
    @foreach ($items as $item)
        <li>
            {{ $item }}
        </li>
    @endforeach
    {!! ($style == 'unordered') ? '</ul>' : '</ol>' !!}
  </div>
</div>
