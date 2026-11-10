$(document).on("select2:open", function (e) {
    var select = $(e.target);
    if (select.attr("multiple")) {
        var s2 = select.data("select2");
        var dropdown = s2 && s2.$dropdown ? s2.$dropdown.find(".select2-dropdown") : $(".select2-dropdown");
        
        if (dropdown.find(".select2-search--dropdown").length === 0) {
            var searchHtml =
                '<span class="select2-search select2-search--dropdown">' +
                '<input class="select2-search__field custom-search" type="search" tabindex="-1" autocomplete="off" autocorrect="off" autocapitalize="none" spellcheck="false" role="searchbox" aria-autocomplete="list" placeholder="Ketik kata pencarian...">' +
                '</span>';

            dropdown.prepend(searchHtml);

            var searchInput = dropdown.find(".custom-search");
            var originalSearch = s2 && s2.$selection ? s2.$selection.find(".select2-search__field") : select.parent().find(".select2-search__field");

            searchInput.on("input", function () {
                originalSearch.val($(this).val()).trigger("input");
            });
        }

        var inputEl = dropdown.find(".custom-search");
        inputEl.val("");

        setTimeout(function () {
            if (inputEl.length && inputEl[0].focus) {
                inputEl[0].focus({ preventScroll: true });
            }
        }, 10);
    }
});

$(document).on("select2:close", function (e) {
    var select = $(e.target);
    if (select.attr("multiple")) {
        var s2 = select.data("select2");
        if (s2 && s2.$dropdown) {
            s2.$dropdown.find(".custom-search").val("");
        }
    }
});
