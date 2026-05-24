<footer class="bg-[#045F33] pt-16 pb-8 text-white text-sm md:text-base">
    <div class="container mx-auto px-4 md:px-8">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 mb-12">
            
            <!-- Yayasan -->
            <div>
                <h4 class="text-[#F2C94C] font-bold text-lg mb-6 uppercase tracking-wider">YAYASAN AL-MACHMUDI BANTANY</h4>
                <p class="text-green-100 leading-relaxed mb-6 font-light">
                    {{ isset($settings) && !empty($settings->footer_text) ? $settings->footer_text : "Pondok Pesantren Nurul Khidmah adalah lembaga pendidikan Islam terpadu yang bertekad mencetak generasi qur'ani yang mandiri, berakhlak mulia, dan berdaya saing global." }}
                </p>
                <!-- Social Icons -->
                <div class="flex space-x-4">
                    <!-- Instagram -->
                    <a href="#" class="w-8 h-8 rounded-full bg-[#F2C94C] flex items-center justify-center text-[#045F33] hover:bg-yellow-400 transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                    <!-- YouTube -->
                    <a href="#" class="w-8 h-8 rounded-full bg-[#F2C94C] flex items-center justify-center text-[#045F33] hover:bg-yellow-400 transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M19.615 3.184c-3.604-.246-11.631-.245-15.23 0-3.897.266-4.356 2.62-4.385 8.816.029 6.185.484 8.549 4.385 8.816 3.6.245 11.626.246 15.23 0 3.897-.266 4.356-2.62 4.385-8.816-.029-6.185-.484-8.549-4.385-8.816zm-10.615 12.816v-8l8 3.993-8 4.007z"/></svg>
                    </a>
                    <!-- Facebook -->
                    <a href="#" class="w-8 h-8 rounded-full bg-[#F2C94C] flex items-center justify-center text-[#045F33] hover:bg-yellow-400 transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M22.675 0h-21.35C.597 0 0 .597 0 1.325v21.351C0 23.403.597 24 1.325 24h11.495v-9.294h-3.128v-3.622h3.128V8.413c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12V24h6.116c.73 0 1.323-.597 1.323-1.325V1.325C24 .597 23.403 0 22.675 0z"/></svg>
                    </a>
                    <!-- TikTok -->
                    <a href="#" class="w-8 h-8 rounded-full bg-[#F2C94C] flex items-center justify-center text-[#045F33] hover:bg-yellow-400 transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.95v7.4c-.01 1.96-.81 3.86-2.18 5.25-1.38 1.39-3.26 2.19-5.23 2.19-2.02 0-3.97-.83-5.38-2.31-1.35-1.44-2.07-3.41-2.01-5.41.05-2.06.88-4.01 2.31-5.42 1.4-1.37 3.32-2.12 5.27-2.12.39 0 .78.03 1.16.1v4.06c-.4-.08-.8-.11-1.21-.11-1.03.01-2.02.44-2.73 1.17-.72.74-1.12 1.73-1.12 2.76 0 1.05.42 2.06 1.15 2.79.74.74 1.74 1.14 2.8 1.12 1.02-.02 2-.45 2.7-1.18.71-.73 1.1-1.72 1.1-2.75v-15.6z"/></svg>
                    </a>
                </div>
            </div>

            <!-- Unit Pendidikan -->
            <div>
                <h4 class="text-[#F2C94C] font-bold text-lg mb-6 uppercase tracking-wider">UNIT PENDIDIKAN</h4>
                <ul class="space-y-3 text-green-100 font-light">
                    <li>• Madrasah Tsanawiyah (MTs)</li>
                    <li>• Madrasah Aliyah (MA)</li>
                    <li>• SMK Otomotif</li>
                    <li>• SMK Desain Komunikasi Visual</li>
                </ul>

                <h4 class="text-[#F2C94C] font-bold text-lg mt-8 mb-4 uppercase tracking-wider">JAM OPERASIONAL</h4>
                <p class="text-green-100 font-light">Senin - Sabtu: 08:00 - 15:00 WIB</p>
            </div>

            <!-- Sekretariat -->
            <div>
                <h4 class="text-[#F2C94C] font-bold text-lg mb-6 uppercase tracking-wider">SEKRETARIAT & CONTACT</h4>
                
                <div class="flex items-center justify-center md:justify-start gap-3 text-green-100 mb-3 font-light">
                    <svg class="w-5 h-5 text-[#F2C94C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>{{ isset($settings) && !empty($settings->email) ? $settings->email : 'Sekretariat@media.ppnk' }}</span>
                </div>

                <div class="flex items-center justify-center md:justify-start gap-3 text-green-100 mb-8 font-light">
                    <svg class="w-5 h-5 text-[#F2C94C]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>{{ isset($settings) && !empty($settings->whatsapp) ? $settings->whatsapp : '08 54 77 68 98 11' }}</span>
                </div>

                <h4 class="text-[#F2C94C] font-bold text-lg mb-4 uppercase tracking-wider text-center md:text-left">ALAMAT LEMBAGA</h4>
                <div class="flex items-start justify-center md:justify-start gap-3 text-green-100 font-light text-center md:text-left">
                    <svg class="w-5 h-5 text-[#F2C94C] mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <p>{{ isset($settings) && !empty($settings->address) ? $settings->address : 'Wringinanom, Lopang, Kembang Bahu, Kabupaten Lamongan, Jawa Timur' }}</p>
                </div>
            </div>

        </div>

        <div class="border-t border-green-800 pt-8 text-center text-green-200 text-xs md:text-sm font-light">
            &copy; {{ date('Y') }} Pondok Pesantren Nurul Khidmah - Hak Cipta Dilindungi Undang-Undang.
        </div>
    </div>
</footer>
