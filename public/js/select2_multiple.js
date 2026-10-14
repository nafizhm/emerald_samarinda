$(document).on("select2:open", function (e) {
    var select = $(e.target);
    if (select.attr("multiple")) {
        var dropdown = $(".select2-dropdown");
        if (dropdown.find(".select2-search--dropdown").length === 0) {
            var isDark = $("body").hasClass("dark");
            var inputStyle = isDark
                ? "width: 100%; border: 1px solid #444; border-radius: 0.25rem; padding: 4px 8px; background-color: #1e1e2d; color: #fff;"
                : "width: 100%; border: 1px solid #ced4da; border-radius: 0.25rem; padding: 4px 8px;";

            var searchHtml =
                '<span class="select2-search select2-search--dropdown" style="padding: 8px; display: block;"><input class="select2-search__field custom-search" type="search" tabindex="-1" autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false" role="searchbox" aria-autocomplete="list" style="' +
                inputStyle +
                '"></span>';

            dropdown.prepend(searchHtml);

            var searchInput = dropdown.find(".custom-search");
            var originalSearch = select
                .data("select2")
                .$selection.find(".select2-search__field");

            searchInput.on("input", function () {
                originalSearch.val($(this).val()).trigger("input");
            });

            setTimeout(function () {
                searchInput.focus();
            }, 10);
        }
    }
});
