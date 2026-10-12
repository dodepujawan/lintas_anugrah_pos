@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleButtons = document.querySelectorAll('.tree-toggle');
        const expandAllButton = document.getElementById('treeExpandAllButton');
        const collapseAllButton = document.getElementById('treeCollapseAllButton');
        const treeStateKey = `report-tree-expanded:${window.location.pathname}`;

        function saveTreeState(expanded) {
            try {
                window.localStorage.setItem(treeStateKey, expanded ? '1' : '0');
            } catch (error) {
                // Penyimpanan browser mungkin dinonaktifkan; tree tetap berfungsi.
            }
        }

        function loadTreeState(defaultExpanded) {
            try {
                const savedState = window.localStorage.getItem(treeStateKey);

                if (savedState === '1') return true;
                if (savedState === '0') return false;
            } catch (error) {
                // Gunakan pilihan bawaan jika penyimpanan tidak tersedia.
            }

            return defaultExpanded;
        }

        function setVisibility(itemId, visible) {
            document.querySelectorAll(`.tree-row[data-parent-id='${itemId}']`).forEach(function(row) {
                row.style.display = visible ? '' : 'none';
                const icon = row.querySelector('.tree-toggle-icon');

                if (icon) {
                    icon.classList.toggle('bi-chevron-down', visible);
                    icon.classList.toggle('bi-chevron-right', !visible);
                }

                if (!visible) {
                    setVisibility(row.getAttribute('data-id'), false);
                }
            });
        }

        function setAllExpanded(expanded) {
            document.querySelectorAll('.tree-row').forEach(function(row) {
                const parentId = row.getAttribute('data-parent-id');
                const icon = row.querySelector('.tree-toggle-icon');
                const keepExpandedOnMobile = isMobile && row.closest('.tree-mobile-expanded');
                const isPersistent = row.hasAttribute('data-tree-persistent');

                if (isPersistent || !parentId) {
                    row.style.display = '';
                } else {
                    row.style.display = (expanded || keepExpandedOnMobile) ? '' : 'none';
                }

                if (icon) {
                    icon.classList.toggle('bi-chevron-down', expanded);
                    icon.classList.toggle('bi-chevron-right', !expanded);
                }
            });

        }

        toggleButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                const itemId = this.getAttribute('data-id');
                const icon = this.querySelector('.tree-toggle-icon');
                const isExpanded = icon.classList.contains('bi-chevron-down');

                if (isExpanded) {
                    icon.classList.remove('bi-chevron-down');
                    icon.classList.add('bi-chevron-right');
                    setVisibility(itemId, false);
                } else {
                    icon.classList.remove('bi-chevron-right');
                    icon.classList.add('bi-chevron-down');
                    setVisibility(itemId, true);
                }
            });
        });

        if (expandAllButton) {
            expandAllButton.addEventListener('click', function() {
                setAllExpanded(true);
                saveTreeState(true);
            });
        }

        if (collapseAllButton) {
            collapseAllButton.addEventListener('click', function() {
                setAllExpanded(false);
                saveTreeState(false);
            });
        }

        const isMobile = window.matchMedia('(max-width: 768px)').matches;
        setAllExpanded(loadTreeState(!isMobile));
    });
</script>
@endpush
