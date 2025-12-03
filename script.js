$(function(){
  // ... existing handlers for like, dislike, delete remain unchanged ...

  // helper to escape text for HTML
  function escapeHtml(text) {
    if (text === null || text === undefined) return '';
    return $('<div/>').text(text).html().replace(/\n/g, '<br>');
  }

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
          let img = p.image ? `<img class='post-image' src='${encodeURI(p.image)}'>` : '';
          let html = `<div class='card post' data-id='${p.id}'>
                        <div class='meta'><div class='name'>${escapeHtml(p.full_name || '')}</div>
                        <div class='time timestamp'>${escapeHtml(p.created_at || '')}</div></div>
                        <div class='body'>
                          <p>${escapeHtml(p.description || '')}</p>
                          ${img}
                          <div class='controls'>
                            <button class='control-btn like' data-id='${p.id}'>👍 Like <span class='count'>${p.likes ?? 0}</span></button>
                            <button class='control-btn dislike' data-id='${p.id}'>👎 Dislike <span class='count'>${p.dislikes ?? 0}</span></button>
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

  // ... rest of your JS (profile edit, update profile) ...
});
