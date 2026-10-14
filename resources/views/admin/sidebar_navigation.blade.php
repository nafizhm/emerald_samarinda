@php
    $navigation = app(\App\Services\SidebarNavigation::class)->forUser((int) auth()->id(), request()->route()?->getName());
    $previousSection = null;
@endphp
@foreach($navigation as $item)
    @if($item['section'] && $item['section'] !== $previousSection)
        <li class="sidebar-title">{{ $item['section'] }}</li>
    @endif
    @php $previousSection = $item['section']; @endphp
    @if($item['children'])
        <li class="sidebar-item has-sub {{ $item['active'] ? 'active' : '' }}">
            <a href="#" class="sidebar-link" title="{{ $item['title'] }}" aria-expanded="{{ $item['active'] ? 'true' : 'false' }}" aria-controls="navigation-{{ $item['id'] }}">
                <i class="{{ $item['icon'] }}"></i><span>{{ $item['title'] }}</span>
            </a>
            <ul id="navigation-{{ $item['id'] }}" class="submenu {{ $item['active'] ? 'submenu-open' : 'submenu-closed' }}">
                @foreach($item['children'] as $child)
                    <li class="submenu-item {{ $child['active'] ? 'active' : '' }}">
                        <a href="{{ route($child['route']) }}" class="submenu-link" title="{{ $child['title'] }}" @if($child['active']) aria-current="page" @endif>{{ $child['title'] }}</a>
                    </li>
                @endforeach
            </ul>
        </li>
    @else
        <li class="sidebar-item {{ $item['active'] ? 'active' : '' }}">
            <a href="{{ route($item['route']) }}" class="sidebar-link" title="{{ $item['title'] }}" @if($item['active']) aria-current="page" @endif><i class="{{ $item['icon'] }}"></i><span>{{ $item['title'] }}</span></a>
        </li>
    @endif
@endforeach
@push('scripts')
<script>
document.querySelectorAll('.sidebar-item.has-sub').forEach(function(item) {
    const submenu = item.querySelector('.submenu');
    const link = item.querySelector('.sidebar-link');
    link.addEventListener('click', function() {
        if (!submenu.classList.contains('submenu-open')) return;
        document.querySelectorAll('.sidebar-item.has-sub .submenu-open').forEach(function(other) {
            if (other !== submenu) {
                other.classList.remove('submenu-open');
                other.classList.add('submenu-closed');
            }
        });
    });
    new MutationObserver(function() {
        link.setAttribute('aria-expanded', submenu.classList.contains('submenu-open') ? 'true' : 'false');
    }).observe(submenu, {attributes:true, attributeFilter:['class']});
});
</script>
@endpush
