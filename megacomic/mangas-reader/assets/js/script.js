$(document).ready(function () {
    $('.select2-tag').select2({
      placeholder: "Selecciona categorías",
      allowClear: true,
      width: '100%',
      closeOnSelect: false,
      language: {
        noResults: function () {
          return "No se encontraron resultados";
        },
        searching: function () {
          return "Buscando...";
        }
      },
      templateSelection: function (data) {
        if (!data.id) return data.text;
        return $('<span class="badge bg-primary me-1 mb-1">' + data.text + ' <span class="ms-1" style="cursor:pointer;" data-id="' + data.id + '">&times;</span></span>');
      }
    });
  
    // Eliminar una categoría desde el tag visual
    $('#categorias').on('select2:select select2:unselect', function () {
      // Re-render para aplicar templateSelection
      $(this).trigger('change.select2');
    });
  
    // Escuchar clic en la X de cada tag y deseleccionar el elemento
    $(document).on('click', '.select2-selection__rendered span[data-id]', function () {
      var id = $(this).data('id');
      var option = $('#categorias option[value="' + id + '"]');
      option.prop('selected', false);
      $('#categorias').trigger('change');
    });
  });

  