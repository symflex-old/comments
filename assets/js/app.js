$(function () {

    let url = new URL(location.href);
    let params = new URLSearchParams(url.search);
    let postId = params.get('post_id');
    let createUrl = '/php/?action=create&post_id=' + postId;

    function updateComments() {
        $.ajax({
            url: '/php/?post_id=' + params.get('post_id'),
            dataType: 'json'
        }).done(function (result) {
            let comments = createTreeComments(result, false);
            $('.comments').html(comments)
        });
    }

    if (postId) {
        updateComments();
    }

    function createTreeComments(comments, isChild) {
        let tree = '';

        if (isChild) {
            tree += '<ul>';
        }

        for (let comment of comments) {
            tree += '<li data-comment-id="' + comment.id + '">';
            tree += '<div class="comment">';
            tree += '<span>Дата: ';
            tree += comment.date;
            tree += '</span>';
            tree += '<div class="text">';
            tree += comment.text
            tree += '</div>';
            tree += '<div class="reply" data-reply-post-id="' + comment.post_id + '" data-reply-comment-id="' + comment.id + '">Ответить</div>';
            tree += '</div>';
            if (comment.hasOwnProperty('children')) {
                tree += createTreeComments(comment.children, true);
            }
            tree += '</li>';
        }
        if (isChild) {
            tree += '</ul>';
        }
        return tree;
    }

    // показать форму ответа на комментарий
    $('.comments').on('click', '.reply', function (e) {
        $(this).hide();
        let commentId = $(this).data('reply-comment-id');

        let form = '<div data-form-reply-id="' + commentId + '">';
        form += '<div class="form-group">';
        form += '<textarea class="form-control" required></textarea>';
        form += '</div>';
        form += '<div class="group-button">';
        form += '<div class="btn btn-primary btn-sm cancel-reply" data-id="' + commentId + '">Отмена</div>';
        form += '<div class="btn btn-success btn-sm send-reply" data-id="' + commentId + '">Отправить</div>';
        form += '</div>';
        form += '</div>';

        $(this).after(form)
    })

    // скрыть форму ответа на комментарий
    $('.comments').on('click', '.cancel-reply', function (e) {
        let id = $(this).data('id');
        $('.comments').find('[data-reply-comment-id="' + id + '"]').show();
        $('.comments').find('[data-form-reply-id="' + id + '"]').remove();
    })

    // данные из формы ответа на комментарий
    $('.comments').on('click', '.send-reply', function (e) {

        let comment_id = $(this).data('id');
        let formEl = $('.comments').find('[data-form-reply-id="' + comment_id + '"]');
        let commentText = $(formEl).find('textarea').val();

        $.ajax({
            url: createUrl,
            dataType: 'json',
            method: 'post',
            data: {comment_id: comment_id, text: commentText}
        }).done(function (response) {
            //что бы не лепить код для вставки комментария в верстку просто делаем запрос и обновляем.
            updateComments();
            $(formEl).remove();
            $('.comments').find('[data-reply-comment-id="' + comment_id + '"]').show();
        });
    })

    // данные из формы в верху страницы
    $('form').on('submit', function (e) {
        e.preventDefault();

        if (!postId) {
            alert('Укажите в строке адреса ?post_id=1');
            return;
        }

        let _this = this;
        $.ajax({
            url: createUrl,
            dataType: 'json',
            method: 'post',
            data: $(this).serialize()
        }).done(function (response) {
            //что бы не лепить код для вставки комментария в верстку просто делаем запрос и обновляем.
            $(_this).trigger('reset');
            updateComments();
        });
    });
});
