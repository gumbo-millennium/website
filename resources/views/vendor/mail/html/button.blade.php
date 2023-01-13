<table class="action" align="center" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="center" class="py-4 px-4">
{{-- This is illegal, but it's email --}}
<x-button href="{{ $url }}" style="primary" target="_blank" rel="nofollow noopener noreferrer">
{{ $slot }}
</x-button>
</td>
</tr>
</table>
</td>
</tr>
</table>
