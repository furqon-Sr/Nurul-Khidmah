@php
    $heroes = \App\Models\Hero::orderBy('position')->get();
@endphp

@if($heroes->count() > 0)
    <section x-data="{ 
        activeSlide: 0, 
        slidesCount: {{ $heroes->count() }},
        autoplay() {
            setInterval(() => {
                this.activeSlide = (this.activeSlide + 1) % this.slidesCount;
            }, 6000);
        }
    }" x-init="autoplay()" class="relative bg-gradient-to-r from-primary-700 to-primary-900 min-h-[500px] md:min-h-[600px] flex items-center overflow-hidden">
        
        <!-- Slides -->
        @foreach($heroes as $index => $hero)
            <div x-show="activeSlide === {{ $index }}" 
                 x-transition:enter="transition ease-out duration-1000"
                 x-transition:enter-start="opacity-0 transform scale-105"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-500"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 z-0">
                 
                <!-- Background Image covering full width -->
                <div class="absolute inset-0 w-full h-full bg-black/25 mix-blend-multiply" style="background-image: url('{{ Storage::url($hero->image_path) }}'); background-size: cover; background-position: center;"></div>
                <!-- Smooth gradient overlay: emerald green gradient on the left, fading to transparent on the right -->
                <div class="absolute inset-0 bg-gradient-to-r from-primary-700 via-primary-900 via-45% to-transparent"></div>
            </div>
        @endforeach

        <div class="container mx-auto px-4 md:px-8 relative z-10 w-full">
            @foreach($heroes as $index => $hero)
                <div x-show="activeSlide === {{ $index }}" 
                     x-transition:enter="transition ease-out duration-700 delay-300"
                     x-transition:enter-start="opacity-0 translate-y-4"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     class="max-w-2xl">
                    <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-white mb-6 leading-tight">
                        {!! nl2br(e($hero->title)) !!}
                    </h1>
                    
                    <p class="text-white/90 mb-10 text-lg md:text-xl font-medium">
                        Mencetak santri mandiri yang siap menghadapi tantangan zaman dengan penguasaan dan keunggulan akademik.
                    </p>
                    
                    <div class="flex flex-wrap gap-4">
                        <a href="{{ route('registration') }}" class="inline-flex items-center justify-center px-8 py-3 rounded-xl bg-secondary-500 text-gray-900 font-extrabold text-base hover:bg-secondary-400 transition shadow-lg shadow-black/10">
                            Daftar PPDB
                        </a>
                        @if(isset($contents['education_fee_pdf']) && $contents['education_fee_pdf']?->image_path)
                            <a href="{{ route('education.download-fee') }}" target="_blank"
                               class="inline-flex items-center gap-2 justify-center px-8 py-3 rounded-xl border-2 border-white text-white font-bold text-base hover:bg-white/10 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                Biaya Pendidikan
                            </a>
                        @else
                            <a href="{{ route('education') }}"
                               class="inline-flex items-center gap-2 justify-center px-8 py-3 rounded-xl border-2 border-white/60 text-white/60 font-bold text-base cursor-not-allowed">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Biaya Pendidikan
                            </a>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Dot Indicators -->
        @if($heroes->count() > 1)
            <div class="absolute bottom-6 left-1/2 transform -translate-x-1/2 z-20 flex space-x-3">
                @foreach($heroes as $index => $hero)
                    <button @click="activeSlide = {{ $index }}" 
                            :class="activeSlide === {{ $index }} ? 'bg-secondary-500 w-8' : 'bg-white/50 w-3'" 
                            class="h-3 rounded-full transition-all duration-300"></button>
                @endforeach
            </div>
        @endif
    </section>
@else
    <!-- Fallback static hero section if no heroes added in database -->
    <section class="relative bg-gradient-to-r from-primary-700 to-primary-900 py-24 md:py-32 lg:py-40">
        <div class="absolute inset-0 z-0 overflow-hidden">
            <!-- Background Image covering full width -->
            <div class="absolute inset-0 w-full h-full bg-black/20 mix-blend-multiply" style="background-image: url('https://images.unsplash.com/photo-1601625463695-1f9e2ed36b8e?q=80&w=1470&auto=format&fit=crop'); background-size: cover; background-position: center; opacity: 0.4;"></div>
            <!-- Smooth gradient overlay: emerald green gradient on the left, fading to transparent on the right -->
            <div class="absolute inset-0 bg-gradient-to-r from-primary-700 via-primary-900 via-45% to-transparent"></div>
        </div>

        <div class="container mx-auto px-4 md:px-8 relative z-10">
            <div class="max-w-2xl">
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold tracking-tight text-white mb-6 leading-tight">
                    Berilmu Amaliyah,<br/>
                    Beramal Ilmiyah
                </h1>
                
                <p class="text-white/90 mb-10 text-lg md:text-xl font-medium">
                    Mencetak santri mandiri yang siap menghadapi tantangan zaman dengan penguasaan dan keunggulan akademik.
                </p>
                
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('registration') }}" class="inline-flex items-center justify-center px-8 py-3 rounded-xl bg-secondary-500 text-gray-900 font-extrabold text-base hover:bg-secondary-400 transition shadow-lg shadow-black/10">
                        Daftar PPDB
                    </a>
                    @if(isset($contents['education_fee_pdf']) && $contents['education_fee_pdf']?->image_path)
                        <a href="{{ route('education.download-fee') }}" target="_blank"
                           class="inline-flex items-center gap-2 justify-center px-8 py-3 rounded-xl border-2 border-white text-white font-bold text-base hover:bg-white/10 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Biaya Pendidikan
                        </a>
                    @else
                        <span class="inline-flex items-center gap-2 justify-center px-8 py-3 rounded-xl border-2 border-white/50 text-white/50 font-bold text-base">
                            Biaya Pendidikan
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endif
