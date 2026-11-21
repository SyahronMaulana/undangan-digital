@php($template = $wedding->template->slug ?? 'melati')
@includeIf('invitations.templates.'.$template, ['wedding' => $wedding, 'guest' => $guest])
