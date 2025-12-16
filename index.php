<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Customer Information | CURD App</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    .error { color: #d9534f; font-size: 0.9em; }
    img.profile-thumb { width:50px; height:50px; object-fit:cover; border-radius:4px; }
  </style>
</head>
<body>
<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3>Customers Information</h3>
    <button id="addNewBtn" class="btn btn-primary">Add Customer</button>
  </div>

  <div class="row mb-3">
    <div class="col-md-4">
      <input id="search" class="form-control" placeholder="Search by name or email or mobile">
    </div>
  </div>

  <div id="table-area"></div>
</div>

<!-- Modal -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <form id="userForm" enctype="multipart/form-data" novalidate>
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitle">Add Customer</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" id="action" value="add">
          <input type="hidden" name="id" id="user_id" value="">

          <div id="formAlert"></div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Name *</label>
              <input type="text" name="name" id="name" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Email *</label>
              <input type="email" name="email" id="email" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Mobile (10 digits) *</label>
              <input type="text" name="mobile" id="mobile" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label class="form-label d-block">Gender *</label>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="gender" id="g_m" value="Male">
                <label class="form-check-label" for="g_m">Male</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="gender" id="g_f" value="Female">
                <label class="form-check-label" for="g_f">Female</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input" type="radio" name="gender" id="g_o" value="Other">
                <label class="form-check-label" for="g_o">Other</label>
              </div>
            </div>

            <div class="col-md-12">
              <label class="form-label d-block">Hobbies * (choose at least 1)</label>
              <div class="form-check form-check-inline">
                <input class="form-check-input hobby" type="checkbox" name="hobbies[]" id="h1" value="Reading">
                <label class="form-check-label" for="h1">Reading</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input hobby" type="checkbox" name="hobbies[]" id="h2" value="Travel">
                <label class="form-check-label" for="h2">Travel</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input hobby" type="checkbox" name="hobbies[]" id="h3" value="Cooking">
                <label class="form-check-label" for="h3">Cooking</label>
              </div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Language *</label>
              <select name="language" id="language" class="form-select" required>
                <option value="">Select</option>
                <option value="Hindi">Hindi</option>
                <option value="English">English</option>
                <option value="Bhojpuri">Bhojpuri</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Skill(s) * (select 1 or more)</label>
              <select name="skill[]" id="skill" class="form-select" multiple required>
                <option value="PHP">PHP</option>
                <option value="Laravel">Laravel</option>
                <option value="MySQL">MySQL</option>
                <option value="jQuery">jQuery</option>
                <option value="React">React</option>
                <option value="Python">Python</option>
              </select>
              <div class="form-text">Use Ctrl/Cmd to select multiple</div>
            </div>

            <div class="col-md-6">
              <label class="form-label">Profile Image * (required on Add)</label>
              <input type="file" name="image" id="image" accept="image/*" class="form-control">
              <div id="currentImage" class="mt-2"></div>
            </div>

          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button type="submit" id="saveBtn" class="btn btn-primary">Save</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
$(function(){
  const $modal = new bootstrap.Modal(document.getElementById('userModal'), {});
  let currentPage = 1;
  const perPage = 10;

  // fetch table
  function fetchTable(page = 1, query = '') {
    currentPage = page;
    $.ajax({
      url: 'get_ajax.php',
      method: 'POST',
      data: { action: 'fetch', page: page, per_page: perPage, q: query },
      dataType: 'json',
      beforeSend(){ $('#table-area').html('<div class="text-center p-3">Loading...</div>'); },
      success(res){
        if(res.status === 'success'){
          $('#table-area').html(res.html);
        } else {
          $('#table-area').html('<div class="text-danger p-3">No data</div>');
        }
      }
    });
  }
  fetchTable();
  $('#search').on('keyup', function(){
    const q = $(this).val();
    fetchTable(1, q);
  });
  $('#addNewBtn').on('click', function(){
    $('#userForm')[0].reset();
    $('#action').val('add');
    $('#user_id').val('');
    $('#modalTitle').text('Add Customer');
    $('#currentImage').html('');
    $('#formAlert').html('');
    $('input[name="gender"][value="Male"]').prop('checked', true);
    $modal.show();
  });
  $(document).on('click', '.page-link', function(e){
    e.preventDefault();
    const page = $(this).data('page');
    const q = $('#search').val();
    if(page) fetchTable(page, q);
  });
  $(document).on('click', '.btn-edit', function(){
    const id = $(this).data('id');
    $.ajax({
      url: 'get_ajax.php',
      method: 'POST',
      data: { action: 'edit_fetch', id: id },
      dataType: 'json',
      success(res){
        if(res.status === 'success'){
          const d = res.data;
          $('#action').val('update');
          $('#user_id').val(d.id);
          $('#name').val(d.name);
          $('#email').val(d.email);
          $('#mobile').val(d.mobile);
          $('input[name="gender"][value="'+d.gender+'"]').prop('checked', true);
          // hobbies (checkboxes)
          $('input[name="hobbies[]"]').prop('checked', false);
          if(d.hobbies){
            const h = d.hobbies.split(',');
            h.forEach(v => {
              $('input[name="hobbies[]"][value="'+v+'"]').prop('checked', true);
            });
          }
          $('#language').val(d.language);
          // skill (multiple)
          $('#skill').val(d.skill ? d.skill.split(',') : []);
          $('#currentImage').html(d.image ? '<img src="uploads/'+d.image+'" class="profile-thumb">': '');
          $('#modalTitle').text('Edit Customer');
          $('#formAlert').html('');
          $modal.show();
        } else {
          alert(res.message || 'Unable to fetch data');
        }
      }
    });
  });

  // delete
  $(document).on('click', '.btn-delete', function(){
    if(!confirm('Are you sure to delete this record?')) return;
    const id = $(this).data('id');
    $.ajax({
      url: 'get_ajax.php',
      method: 'POST',
      data: { action: 'delete', id: id },
      dataType: 'json',
      success(res){
        if(res.status === 'success'){
          fetchTable(currentPage, $('#search').val());
        } else {
          alert(res.message || 'Delete failed');
        }
      }
    });
  });

  // custom validator for hobbies (at least one)
  $.validator.addMethod("hobbyRequired", function(value, element) {
    return $('input[name="hobbies[]"]:checked').length > 0;
  }, "Please select at least one hobby.");

  // custom validator for skill (at least one)
  $.validator.addMethod("skillRequired", function(value, element) {
    try {
      const val = $('#skill').val();
      return val && val.length > 0;
    } catch(e){ return false; }
  }, "Please select at least one skill.");

  // validate form
  $('#userForm').validate({
    ignore: [],
    rules: {
      name: { required: true, minlength: 2 },
      email: { required: true, email: true },
      mobile: { required: true, digits: true, minlength: 10, maxlength: 10 },
      'gender': { required: true },
      'hobbies[]': { hobbyRequired: true },
      language: { required: true },
      'skill[]': { skillRequired: true },
    },
    messages: {
      mobile: { minlength: "Enter 10 digits", maxlength: "Enter 10 digits" }
    },
    submitHandler: function(form){
      const formData = new FormData(form);
      const action = $('#action').val();
      const fileInput = $('#image')[0];
      if(action === 'add'){
        if(!fileInput.files || fileInput.files.length === 0){
          $('#formAlert').html('<div class="alert alert-danger">Profile image is required.</div>');
          return false;
        }
      }

      $.ajax({
        url: 'get_ajax.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        beforeSend(){ $('#saveBtn').prop('disabled', true).text('Saving...'); },
        success(res){
          $('#saveBtn').prop('disabled', false).text('Save');
          if(res.status === 'success'){
            $modal.hide();
            fetchTable(currentPage, $('#search').val());
          } else if(res.status === 'validation'){
            let html = '<div class="alert alert-danger"><ul>';
            res.errors.forEach(e => html += '<li>'+e+'</li>');
            html += '</ul></div>';
            $('#formAlert').html(html);
          } else {
            $('#formAlert').html('<div class="alert alert-danger">'+(res.message || 'Something went wrong')+'</div>');
          }
        },
        error(){
          $('#saveBtn').prop('disabled', false).text('Save');
          $('#formAlert').html('<div class="alert alert-danger">Server error</div>');
        }
      });
      return false;
    }
  });

});
</script>
</body>
</html>
