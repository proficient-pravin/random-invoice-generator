<!-- Tag Button with Dynamic Background Color -->
<button class="px-4 py-2 rounded-full text-white text-sm" 
        style="background-color: {{ $tag->bg_color }};">
    {{ $tag->name }}
</button>
