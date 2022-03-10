<div class="editor-js-block">
  <div class="container container--md">
    <figure>
      <table class="editor-js-table">
          @foreach ($content as $row)
              <tr>
                  @foreach ($row as $content)
                      <td>
                          {{ $content }}
                      </td>
                  @endforeach
              </tr>
          @endforeach
      </table>
    </figure>
  </div>
</div>
