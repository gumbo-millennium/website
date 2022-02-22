<?php
$title = isset($title) ? $title : 'Gumbo Millennium';
$header = isset($header) ? $header : null;
$image = isset($image) ? $image : '';

$coverImage = image_asset($image)->square(1920)->height(768);
$coverImageSmall = image_asset($image)->square(768)->height(300);
?>
<div class="relative">
    @if ($image)
    <picture class="absolute inset-0 h-[300px] md:h-[500px] bg-brand-900">
        <source srcset="{{ $coverImage->webp() }} 1920w, {{ $coverImageSmall->webp() }} 768w" type="image/webp">
        <source srcset="{{ $coverImage->jpeg() }} 1920w, {{ $coverImageSmall->jpeg() }} 768w" type="image/jpeg">
        <img class="w-full h-[300px] md:h-[500px] object-cover" src="{{ $coverImageSmall->jpeg() }}" alt="{{ $title }}">
    </picture>
    @else
    <div class="absolute inset-0 h-[300px] md:h-[500px] bg-gray-100 flex items-center justify-center">
        <div class="hidden md:block h-[275px] md:h-[300px]">
            <img src="{{ mix('images/logo-text-green.svg') }}" alt="Gumbo Millennium" class="h-32 mx-auto block mb-8">
        </div>
    </div>
    @endif

    <div class="mb-[-16rem] h-[300px] md:h-[500px]"></div>

    <div class="relative z-10 enroll-column pt-8 lg:pt-16">
        @if (flash()->message)
        <div class="mb-4 mt-0" role="alert">
            <div class="notice {{ flash()->class }} bg-white mt-0">
                <p>{{ flash()->message }}</p>
            </div>
        </div>
        @endif

        <div class="enroll-card px-0">
            <div class="flex flex-col w-full md:flex-row mb-8 gap-4">
                <div class="flex flex-col w-full gap-4 px-8">
                    <h1 class="font-title text-3xl">{{ $title }}</h1>
                </div>

                @isset($headerIcon)
                <div class="flex-none px-8">
                    {{ $headerIcon }}
                </div>
                @endisset
            </div>

            @isset($header)
            {{ $header }}
            @endisset

            @if ($slot)
            <div class="mt-4">
                {{ $slot }}
            </div>
            @endif
        </div>
    </div>
</div>
