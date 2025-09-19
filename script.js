$(function(){
  // LIKE
  $(document).on('click', '.like', function(){
    const btn = $(this);
    const id = btn.data('id');
    $.post('ajax.php', {action: 'like', id: id}, function(res){
      if (res.status === 'ok') {
        btn.find('.count').text(res.likes);
      }
    }, 'json');
  });

  // DISLIKE
  $(document).on('click', '.dislike', function(){
    const btn = $(this);
    const id = btn.data('id');
    $.post('ajax.php', {action: 'dislike', id: id}, function(res){
      if (res.status === 'ok') {
        btn.find('.count').text(res.dislikes);
      }
    }, 'json');
  });

  // DELETE POST
  $(document).on('click', '.delete-post', function(){
    if (!confirm('Delete this post?')) return;
    const id = $(this).data('id');
    const card = $(this).closest('.post');
    $.post('ajax.php', {action: 'delete_post', id: id}, function(res){
      if (res.status === 'ok') {
        card.fadeOut(200, function(){ $(this).remove(); });
      } else {
        alert('Could not delete post.');
      }
    }, 'json');
  });

  // SUBMIT NEW POST via AJAX
  $('#new-post-form').on('submit', function(e){
    e.preventDefault();
    const form = $(this)[0];
    const data = new FormData(form);
    data.append('action', 'add_post');
    $('#post-message').text('Posting...');
    $.ajax({
      url: 'ajax.php',
      type: 'POST',
      data: data,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res){
        if (res.status === 'ok') {
          // build new post HTML and prepend
          const p = res.post;
          let img = p.image ? `<img class='post-image' src='${p.image}'>` : '';
          let html = `<div class='card post' data-id='${p.id}'>
                        <div class='meta'><div class='name'>${p.full_name}</div>
                        <div class='time timestamp'>${p.created_at}</div></div>
                        <div class='body'>
                          <p>${escapeHtml(p.description || '')}</p>
                          ${img}
                          <div class='controls'>
                            <button class='control-btn like' data-id='${p.id}'>👍 Like <span class='count'>${p.likes}</span></button>
                            <button class='control-btn dislike' data-id='${p.id}'>👎 Dislike <span class='count'>${p.dislikes}</span></button>
                            <button class='control-btn delete-post' data-id='${p.id}'>🗑️ Delete</button>
                          </div>
                        </div>
                      </div>`;
          $('#posts').prepend(html);
          $('#post-message').text('Posted successfully').delay(1500).fadeOut(400, function(){ $(this).show().text(''); });
          $('#new-post-form')[0].reset();
        } else {
          $('#post-message').text(res.message || 'Failed to post.');
        }
      },
      error: function(){
        $('#post-message').text('Server error.');
      }
    });
  });

  // Edit profile UI toggle
  $('#edit-profile-btn').on('click', function(){
    $('#edit-profile-card').removeClass('hidden').addClass('fade-in');
    window.scrollTo({top:0, behavior:'smooth'});
  });
  $('#cancel-edit').on('click', function(){
    $('#edit-profile-card').addClass('hidden');
  });

  // Update profile via AJAX
  $('#edit-profile-form').on('submit', function(e){
    e.preventDefault();
    const data = new FormData(this);
    data.append('action', 'update_profile');
    $.ajax({
      url: 'ajax.php',
      method: 'POST',
      data: data,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res){
        if (res.status === 'ok') {
          // Update visible fields
          $('.profile-name').text(res.user.full_name);
          $('.profile-avatar img').attr('src', res.user.profile_pic);
          $('#profile-age').text(res.user.age);
          $('#edit-profile-card').addClass('hidden');
        } else {
          alert(res.message || 'Profile update failed');
        }
      },
      error: function(){
        alert('Server error while updating profile.');
      }
    });
  });

  // helper to escape text for HTML
  function escapeHtml(text) {
    if (!text) return '';
    return $('<div/>').text(text).html().replace(/\n/g, '<br>');
  }
});
