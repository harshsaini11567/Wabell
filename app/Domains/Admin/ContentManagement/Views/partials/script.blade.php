<script src="{{asset('admin-assets/vendor/dropify/dropify.min.js')}}"></script>
<script src="{{asset('admin-assets/vendor/tinymce/js/tinymce/tinymce.min.js')}}"></script>

<script>
    $(document).ready(function () {

        $('.tinymce-editor').each(function () {
            let dir = $(this).attr('dir');
            let id = $(this).attr('id');
            
            
            let tinySettings = {
                selector: '#'+id, // selects all textareas with this class
                skin: 'oxide-dark',            // 👈 Dark skin for toolbar and UI
                content_css: 'dark',           // 👈 Dark styling for content area
                height: 350,
                plugins: [
                    'lists',
                    'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
                    'insertdatetime', 'media', 'table', 'wordcount'
                ],
                toolbar: 'undo redo | blocks | ' +
                    'bold italic backcolor | alignleft aligncenter ' +
                    'alignright alignjustify | bullist numlist outdent indent | ' +
                    'removeformat',
                content_style: 'body { font-family:Helvetica,Arial,sans-serif; font-size:16px }',
            };

            // Check if current textarea has data-dir="rtl"
            if ($(this).attr('dir') === 'rtl') {
                tinySettings['directionality'] = 'rtl';
                tinySettings['content_style'] += ' body { direction: rtl; text-align: right; }';
            }

            tinymce.init(tinySettings);
        });

        $('.tinymce-editor-title').each(function () {
            let id = $(this).attr('id');
            let dir = $(this).attr('dir');

            let tinySettings = {
                selector: '#' + id,
                menubar: false,
                toolbar: 'forecolor',         // only font color
                plugins: [],
                skin: 'oxide-dark',
                content_css: 'dark',
                branding: false,
                statusbar: false,
                height: 40,                   // single-line height
                forced_root_block: false,     // no <p> wrapping
                force_br_newlines: false,
                force_p_newlines: false,
                toolbar_location: 'bottom',
                content_style: `
                    body {
                        font-family: Helvetica, Arial, sans-serif;
                        font-size: 16px;
                        background: transparent;
                        padding: 6px 10px;
                        margin: 0;
                        color: #fff;
                        background: rgb(34 47 62);
                        white-space: nowrap;
                        overflow-x: auto;
                        text-overflow: ellipsis;
                    }
                    body[contenteditable="true"]:focus {
                        outline: none;
                    }
                `,
                setup: function (editor) {
                    const textarea = document.getElementById(editor.id);
                    editor.on('init', function () {
                        // Make it behave like an input (no extra margin)
                        editor.getContainer().style.border = '1px solid #555';
                        editor.getContainer().style.borderRadius = '6px';

                        if (textarea.hasAttribute('disabled') || textarea.hasAttribute('readonly')) {
                            // Disable editing by making the body non-editable
                            editor.getBody().setAttribute('contenteditable', false);
                            // Optional: visually show it's disabled
                            editor.getContainer().style.opacity = '0.7';
                        }
                    });

                    // Disable Enter key (no line breaks)
                    editor.on('keydown', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                        }
                    });
                }
            };

            // Apply direction if RTL
            if (dir === 'rtl') {
                tinySettings.directionality = 'rtl';
                tinySettings.content_style += ' body { direction: rtl; text-align: right; }';
            }

            tinymce.init(tinySettings);
        });
    });

    $(document).on('submit', '#contentManagementform', function(e){
        e.preventDefault();
        $('.loader-div').show();
        $(".submitBtn").attr('disabled', true);

        $('.validation-error-block').remove();

        var formData = new FormData(this);

        $('.tinymce-editor').each(function () {
            let id = $(this).attr('id');
            let name = $(this).attr('name');
            
            let content = tinymce.get(id).getContent({ format: 'html' }).trim();
            formData.append(name, content);
        });

        $.ajax({
            type: 'post',
            url: "{{ route('comtent-management.post') }}",
            dataType: 'json',
            contentType: false,
            processData: false,
            data: formData,
            success: function (response) {
                if(response.success) {
                    // toasterAlert('success',response.message);
                    window.location.reload();
                }
            },
            error: function (response) {
                if(response.status === 400){
                    toasterAlert('error',response.responseJSON.error);
                } else if(response.status === 403){
                    toasterAlert('error', "{{trans('messages.access_denied_to_update_page_content')}}");
                } else if(response.status === 500){
                    toasterAlert('error', "{{trans('messages.error_message')}}")
                } else {
                    var errorLabelTitle = '';
                    $.each(response.responseJSON.errors, function (key, item) {
                        errorLabelTitle = '<span class="validation-error-block">'+item[0]+'</span>';

                        $(errorLabelTitle).insertAfter("input[name='"+key+"']");
                    });
                }
            },
            complete: function(res){
                $(".submitBtn").attr('disabled', false);
                $('.loader-div').hide();
            }
        });
    });

    $('.dropify').dropify();
    $('.dropify-errors-container').remove();
    $('.dropify-wrapper').find('.dropify-clear').hide();
</script>