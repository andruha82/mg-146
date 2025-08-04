$(document).ready(function () {
  let originalValues = {};

  $('#toggle-edit-btn').click(function () {
    const editing = $(this).text() === 'Редактировать';

    if (editing) {
      $(this).text('Сохранить');
      $('#cancel-edit-btn').removeClass('d-none');

      $('#personal-info [data-field]').each(function () {
        const field = $(this).data('field');
        const value = $(this).find('.value-text').text().trim();
        originalValues[field] = value;
        let input;

        if (field === 'gender') {
          input = `<select class="form-control form-control-sm" data-field="${field}" style="max-width: 250px;">
            <option value="male"${value === 'Мужской' ? ' selected' : ''}>Мужской</option>
            <option value="female"${value === 'Женский' ? ' selected' : ''}>Женский</option>
            <option value="other"${value === 'Другое' ? ' selected' : ''}>Другое</option>
          </select>`;
        } else if (field === 'country') {
          const countries = ['Украина', 'Молдова', 'Румыния', 'Болгария'];
          input = `<select class="form-control form-control-sm" data-field="${field}" style="max-width: 250px;">`;
          countries.forEach(function (country) {
            const selected = (country === value) ? ' selected' : '';
            input += `<option value="${country}"${selected}>${country}</option>`;
          });
          input += `</select>`;
        } else if (field === 'birth_date') {
          input = `<input type="date" class="form-control form-control-sm" data-field="${field}" value="${value}" autocomplete="off">`;
        } else if (field === 'marketing_consent') {
          const checked = (value === 'Да') ? 'checked' : '';
          input = `<div class="form-check">
            <input class="form-check-input" type="checkbox" data-field="${field}" ${checked} autocomplete="off">
            <label class="form-check-label">да/нет</label>
          </div>`;
        } else {
          input = `<input type="text" class="form-control form-control-sm" data-field="${field}" value="${value}" autocomplete="off">`;
        }

        $(this).find('.value-text').replaceWith(input);
      });

    } else {
      const updatedData = {};
      $('#personal-info input, #personal-info select').each(function () {
        const field = $(this).data('field');
        if ($(this).attr('type') === 'checkbox') {
          updatedData[field] = $(this).is(':checked') ? 1 : 0;
        } else {
          updatedData[field] = $(this).val();
        }
      });

      updatedData['action'] = 'update_user_info';

      $.post('/members/profile_actions.php', updatedData, function (response) {
        if (response.status === 'success' && response.data) {
          const data = response.data;

          const genderMap = {
            'male': 'Мужской',
            'female': 'Женский',
            'other': 'Другое'
          };

          $('#personal-info [data-field]').each(function () {
            const field = $(this).data('field');
            const label = $(this).find('.label-text').prop('outerHTML');
            let displayValue = data[field];

            if (field === 'gender') {
              displayValue = genderMap[data[field]] || '';
            }
            if (field === 'marketing_consent') {
              displayValue = (data[field] == 1) ? 'Да' : 'Нет';
            }

            $(this).html(`${label} <span class="value-text">${displayValue}</span>`);

            if (field === 'name') {
              $('#profile-name').text(data[field]);
            }
          });

          $('#toggle-edit-btn').text('Редактировать');
          $('#cancel-edit-btn').addClass('d-none');
        } else {
          alert('Ошибка: ' + response.message);
        }
      }, 'json');
    }
  });

  $('#cancel-edit-btn').click(function () {
    $('#personal-info [data-field]').each(function () {
      const field = $(this).data('field');
      const label = $(this).find('.label-text').prop('outerHTML');
      const value = originalValues[field] || '';

      $(this).html(`${label} <span class="value-text">${value}</span>`);

      if (field === 'name') {
        $('#profile-name').text(value);
      }
    });

    $('#toggle-edit-btn').text('Редактировать');
    $(this).addClass('d-none');
  });
});
