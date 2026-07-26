<style>
.gs-dropdown {
    position: absolute;
    top: calc(100% + 6px);
    left: 0;
    right: 0;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,.14);
    z-index: 9999;
    max-height: 420px;
    overflow-y: auto;
    display: none;
    font-family: 'Poppins', sans-serif;
}
.gs-dropdown.open { display: block; }
.gs-dropdown .gs-empty {
    padding: 24px 16px;
    text-align: center;
    color: #999;
    font-size: 13px;
}
.gs-dropdown .gs-loading {
    padding: 20px;
    text-align: center;
    color: #7B1D1D;
    font-size: 13px;
}
.gs-category {
    padding: 8px 14px 4px;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .6px;
    text-transform: uppercase;
    color: #aaa;
}
.gs-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 14px;
    text-decoration: none;
    color: #1a202c;
    transition: background .15s;
    border-bottom: 1px solid #f5f5f5;
    cursor: pointer;
}
.gs-item:last-child { border-bottom: none; }
.gs-item:hover { background: #f7fafc; }
.gs-item .gs-icon {
    width: 32px; height: 32px;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}
.gs-item .gs-info { flex: 1; min-width: 0; }
.gs-item .gs-info .gs-title {
    font-size: 13px;
    font-weight: 500;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.gs-item .gs-info .gs-desc {
    font-size: 11px;
    color: #888;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    margin-top: 1px;
}
.gs-item .gs-arrow {
    font-size: 12px;
    color: #ccc;
    flex-shrink: 0;
}
</style>

<script>
(function() {
    const searchUrl = '{{ route("global.search") }}';
    let debounceTimer = null;
    let activeInput = null;
    let activeDropdown = null;
    let activeWrap = null;

    function closeDropdown() {
        if (activeDropdown) {
            activeDropdown.classList.remove('open');
        }
    }

    function closeAll() {
        closeDropdown();
        activeInput = null;
        activeDropdown = null;
        activeWrap = null;
    }

    function openDropdown(input, wrap) {
        let dropdown = wrap.querySelector('.gs-dropdown');
        if (!dropdown) {
            dropdown = document.createElement('div');
            dropdown.className = 'gs-dropdown';
            wrap.style.position = 'relative';
            wrap.appendChild(dropdown);
        }
        activeInput = input;
        activeDropdown = dropdown;
        activeWrap = wrap;
        return dropdown;
    }

    function doSearch(query) {
        if (!activeDropdown || !activeInput) return;
        const dropdown = activeDropdown;
        dropdown.innerHTML = '<div class="gs-loading"><i class="fas fa-spinner fa-spin"></i> Searching...</div>';
        dropdown.classList.add('open');

        fetch(searchUrl + '?q=' + encodeURIComponent(query), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (!activeDropdown || activeInput.value !== query) return;
            if (!data || data.length === 0) {
                dropdown.innerHTML = '<div class="gs-empty">No results found</div>';
                return;
            }

            let html = '';
            let currentCategory = '';
            data.forEach(function(item) {
                if (item.category !== currentCategory) {
                    currentCategory = item.category;
                    html += '<div class="gs-category">' + item.category + '</div>';
                }
                html += '<a class="gs-item" href="' + item.url + '">';
                html += '<div class="gs-icon" style="background:' + (item.color || '#7B1D1D') + '1a;color:' + (item.color || '#7B1D1D') + '"><i class="fas ' + (item.icon || 'fa-search') + '"></i></div>';
                html += '<div class="gs-info"><div class="gs-title">' + escapeHtml(item.title) + '</div>';
                if (item.desc) html += '<div class="gs-desc">' + escapeHtml(item.desc) + '</div>';
                html += '</div><i class="fas fa-chevron-right gs-arrow"></i></a>';
            });
            dropdown.innerHTML = html;
        })
        .catch(function() {
            if (activeDropdown) {
                activeDropdown.innerHTML = '<div class="gs-empty">Search failed. Try again.</div>';
            }
        });
    }

    function escapeHtml(text) {
        var d = document.createElement('div');
        d.textContent = text;
        return d.innerHTML;
    }

    function attachSearch(input) {
        input.addEventListener('input', function() {
            var query = input.value.trim();
            var wrap = input.closest('.gs-wrap') || input.parentElement;
            openDropdown(input, wrap);

            clearTimeout(debounceTimer);
            if (query.length < 2) {
                closeDropdown();
                return;
            }
            debounceTimer = setTimeout(function() { doSearch(query); }, 350);
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeAll();
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('input[data-gs="true"]').forEach(attachSearch);
    });

    // Use mousedown (fires before blur/click) to close dropdown when clicking outside
    document.addEventListener('mousedown', function(e) {
        if (!activeWrap) return;
        if (e.button !== 0) return;
        if (activeWrap.contains(e.target)) return;
        closeAll();
    });
})();
</script>
