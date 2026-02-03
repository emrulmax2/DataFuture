(function () {
    if ($('#editStudentResidencyCriminalForm').length > 0) {
        const editStudentResidencyCriminalModal = tailwind.Modal.getOrCreateInstance(
            document.querySelector('#editStudentResidencyCriminalModal')
        );
        const successModal = tailwind.Modal.getOrCreateInstance(
            document.querySelector('#successModal')
        );

        const toggleConvictionDetails = () => {
            const selected = $(
                '#editStudentResidencyCriminalForm input[name="have_you_been_convicted"]:checked'
            ).val();
            if (selected == 1) {
                $('#editStudentResidencyCriminalForm .criminalConvictionDetailsWrap').fadeIn(
                    'fast'
                );
            } else {
                $('#editStudentResidencyCriminalForm .criminalConvictionDetailsWrap').fadeOut(
                    'fast',
                    function () {
                        $('#editStudentResidencyCriminalForm #criminal_conviction_details').val(
                            ''
                        );
                    }
                );
            }
        };

        toggleConvictionDetails();
        $(document).on(
            'change',
            '#editStudentResidencyCriminalForm input[name="have_you_been_convicted"]',
            toggleConvictionDetails
        );

        $('#editStudentResidencyCriminalForm').on('submit', function (e) {
            e.preventDefault();
            const form = document.getElementById(
                'editStudentResidencyCriminalForm'
            );

            $('#editStudentResidencyCriminalForm .acc__input-error').html('');
            $('#editStudentResidencyCriminalForm .border-danger').removeClass(
                'border-danger'
            );

            document
                .querySelector('#saveStudentResidencyCriminal')
                .setAttribute('disabled', 'disabled');
            document.querySelector(
                '#saveStudentResidencyCriminal svg'
            ).style.cssText = 'display: inline-block;';

            let form_data = new FormData(form);
            axios({
                method: 'post',
                url: route('student.update.residency.criminal'),
                data: form_data,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                },
            })
                .then((response) => {
                    document
                        .querySelector('#saveStudentResidencyCriminal')
                        .removeAttribute('disabled');
                    document.querySelector(
                        '#saveStudentResidencyCriminal svg'
                    ).style.cssText = 'display: none;';

                    if (response.status == 200) {
                        editStudentResidencyCriminalModal.hide();

                        successModal.show();
                        document
                            .getElementById('successModal')
                            .addEventListener('shown.tw.modal', function (event) {
                                $('#successModal .successModalTitle').html(
                                    'Congratulation!'
                                );
                                $('#successModal .successModalDesc').html(
                                    'Residency and Criminal Conviction details successfully updated.'
                                );
                                $('#successModal .successCloser').attr(
                                    'data-action',
                                    'RELOAD'
                                );
                            });

                        setTimeout(function () {
                            successModal.hide();
                            window.location.reload();
                        }, 2000);
                    }
                })
                .catch((error) => {
                    document
                        .querySelector('#saveStudentResidencyCriminal')
                        .removeAttribute('disabled');
                    document.querySelector(
                        '#saveStudentResidencyCriminal svg'
                    ).style.cssText = 'display: none;';

                    if (error.response) {
                        if (error.response.status == 422) {
                            for (const [key, val] of Object.entries(
                                error.response.data.errors
                            )) {
                                $(`#editStudentResidencyCriminalForm .${key}`).addClass(
                                    'border-danger'
                                );
                                $(
                                    `#editStudentResidencyCriminalForm  .error-${key}`
                                ).html(val);
                            }
                        } else {
                            console.log('error');
                        }
                    }
                });
        });
    }
})();
