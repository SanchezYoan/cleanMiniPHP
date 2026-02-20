let config = {};

let helpers = {
    
    /**
     * append csrf token to data that will be sent in ajax
     * @param data
     */
    appendCsrfToken: function (data) {
        
        if (typeof (data) === "string") {
            if (data.length > 0) {
                data = data + "&csrf_token=" + config.csrfToken;
            } else {
                data = data + "csrf_token=" + config.csrfToken;
            }
        } else if (data.constructor.name === "FormData") {
            data.append("csrf_token", config.csrfToken);
        } else if (typeof (data) === "object") {
            data.csrf_token = config.csrfToken;
        }
        
        return data;
    },
    
    /**
     * Extract keys from JavaScript object to be used as variables
     * @param data
     */
    extract: function (data) {
        for (let key in data) {
            window[key] = data[key];
        }
    },
    
    /**
     * Checks if an element is empty(set to null or undefined)
     *
     * @return boolean
     *
     * @param foo
     */
    empty: function (foo) {
        return (foo === null || foo === '' || typeof (foo) === "undefined");
    },
    
    /**
     * extends $().html() in jQuery
     *
     * @param target
     * @param str
     */
    html: function (target, str) {
        $(target).html(str);
    },
    
    /**
     * extends $().after() in jQuery
     *
     * @param target
     * @param str
     */
    after: function (target, str) {
        $(target).after(str);
    },
    
    /**
     * clears all error and success messages
     *
     * @param target
     */
    clearMessages: function (target) {
        
        $("div.error, .alert-error").remove();
        $("div.success, .alert-success").remove();
        
        if (!helpers.empty(target)) {
            $(target).find(".error").remove();
            $(target).find(".success").remove();
            $(target).find(".alert-error").remove();
            $(target).find(".alert-success").remove();
            $(target).nextAll(".error:eq(0)").remove();
            $(target).nextAll(".success:eq(0)").remove();
        }
    },
    
    /**
     * Extend the serialize() function in jQuery.
     * This function is designed to add extra data(name => value) to the form.
     *
     * @return  string          The serialized form data in form of: "name=value&name=value"
     *
     * @param ele
     * @param str
     */
    serialize: function (ele, str) {
        if (helpers.empty(str)) {
            return $(ele).serialize();
        } else {
            return $(ele).serialize() + "&" + str;
        }
    },
    
    /**
     * This function is used to redirect.
     *
     * @param location
     */
    redirectTo: function (location) {
        window.location.href = location;
    },
    
    /**
     * encode potential text
     * All encoding are done and must be done on the server side,
     * but you can use this function in case it's needed on client.
     *
     * @param str
     */
    encodeHTML: function (str) {
        return $('<div />').text(str).html();
    },
    
    sizeConverter: function (aSize) {
        aSize = Math.abs(parseInt(aSize, 10));
        let def = [[1, 'octets'], [1024, 'ko'], [1024 * 1024, 'Mo'], [1024 * 1024 * 1024, 'Go'], [1024 * 1024 * 1024 * 1024, 'To']];
        for (let i = 0; i < def.length; i++) {
            if (aSize < def[i][0]) return (aSize / def[i - 1][0]).toFixed(2) + ' ' + def[i - 1][1];
        }
    },
    
    /**
     * validate form file size
     * It's important to validate file size on client-side to avoid overflow in $_POST & $_FILES
     *
     * @see     app/core/Request/dataSizeOverflow()
     * @param file
     */
    validateFileSize: function (file) {
        let result = {
            ok: true,
            message: "",
        };
        if (undefined !== file) {
            let size = file.size;
            if (size > config.fileSizeOverflow) {
                let humanReadable = helpers.sizeConverter(size);
                result.message = "Fichier " + file.name + " trop lourd " + humanReadable + " ! ";
                result.ok = false;
                return result;
            } else {
                result.message = "";
                result.ok = true;
                return result;
            }
        }
        
        return result;
    },
    
    /**
     * Validate the data coming from server side(PHP)
     *
     * The data coming from PHP should be something like this:
     *      data = [error = "some html code", success = "some html code", data = "some html code", redirect = "link"];
     *
     * @return  boolean
     * @param result
     * @param targetBlock
     * @param errorFunc
     * @param errorType
     * @param returnVal
     */
    validateData: function (result, targetBlock, errorFunc, errorType, returnVal) {
        
        // 1. clear all existing error or success messages
        helpers.clearMessages(targetBlock);
        
        // 2. Define and extend jQuery functions required to display the error.
        if (errorFunc === "html") errorFunc = helpers.html;
        else if (errorFunc === "after") errorFunc = helpers.after;
        else errorFunc = helpers.html;
        
        // 3. check if result is empty
        if (helpers.empty(result)) {
            helpers.displayError(targetBlock);
            return false;
        }
        
        // If there was a redirection
        else if (!helpers.empty(result.redirect)) {
            helpers.redirectTo(result.redirect);
            return false;
        }
        
        // If there was errors encountered and sent from the server, then display it
        else if (!helpers.empty(result.error)) {
            
            if (errorType === "default" || helpers.empty(errorType)) {
                errorFunc(targetBlock, result.error);
            } else if (errorType === "row") {
                let td = $("<td>").attr("colspan", "5");
                errorFunc(targetBlock, $(td).html(result.error));
            }
            
            return false;
        } else {
            
            if (returnVal === "success" && helpers.empty(result.success)) {
                helpers.displayError(targetBlock);
                return false;
            } else if (returnVal === "data" && helpers.empty(result.data)) {
                helpers.displayError(targetBlock);
                return false;
            } else if (returnVal !== "data" && returnVal !== "success") {
                helpers.displayError(targetBlock);
                return false;
            }
        }
        
        return true;
    },
    
    validateURL: function (url) {
        let isUrl = /^((http|https):\/\/)(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(url);
        
        let isEmail = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/i.test(url);
        return isEmail || isUrl;
    },
    
    formatNumber: function (number) {
        let formated = new Intl.NumberFormat('fr-FR').format(number);
        if (/\s/.test(formated)) {
            formated = formated.replace(/\s/g, " ");
        }
        return formated;
    }
    
};

let App = {
    modal: function (type, id, title, content, redirectUrl) {
        // Remove any existing modal with the same id to avoid duplication
        const existingModal = document.getElementById(id);
        if (existingModal) {
            existingModal.remove();
        }
        
        // Create modal elements based on the provided template
        const modal = document.createElement('div');
        modal.classList.add('modal', 'modal-blur', 'fade');
        modal.id = id;
        modal.tabIndex = -1;
        modal.setAttribute('role', 'dialog');
        modal.setAttribute('aria-hidden', 'true');
        
        const modalDialog = document.createElement('div');
        modalDialog.classList.add('modal-dialog', 'modal-sm', 'modal-dialog-centered');
        modalDialog.setAttribute('role', 'document');
        
        const modalContent = document.createElement('div');
        modalContent.classList.add('modal-content');
        
        const modalCloseButton = document.createElement('button');
        modalCloseButton.classList.add('btn-close');
        modalCloseButton.type = 'button';
        modalCloseButton.setAttribute('data-bs-dismiss', 'modal');
        modalCloseButton.setAttribute('aria-label', 'Close');
        
        const modalStatus = document.createElement('div');
        modalStatus.classList.add('modal-status', `bg-${type}`);
        
        const modalBody = document.createElement('div');
        modalBody.classList.add('modal-body', 'text-center', 'py-4');
        modalBody.id = `${id}-body`;
        
        const modalTitle = document.createElement('h3');
        modalTitle.textContent = title;
        
        const modalFooter = document.createElement('div');
        modalFooter.classList.add('modal-footer');
        
        const footerWrapper = document.createElement('div');
        footerWrapper.classList.add('w-100');
        
        const footerRow = document.createElement('div');
        footerRow.classList.add('row');
        
        const closeCol = document.createElement('div');
        closeCol.classList.add('col');
        
        const closeButton = document.createElement('button');
        closeButton.classList.add('btn', `btn-${type}`, 'w-100');
        closeButton.setAttribute('data-bs-dismiss', 'modal');
        closeButton.textContent = config.lang.close;
        
        // Append elements together
        closeCol.appendChild(closeButton);
        footerRow.appendChild(closeCol);
        footerWrapper.appendChild(footerRow);
        modalFooter.appendChild(footerWrapper);
        const iconSuccess = `<svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-green icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"></path><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0"></path><path d="M9 12l2 2l4 -4"></path></svg>`;
        const iconError = `<svg xmlns="http://www.w3.org/2000/svg" class="icon mb-2 text-danger icon-lg" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M10.24 3.957l-8.422 14.06a1.989 1.989 0 0 0 1.7 2.983h16.845a1.989 1.989 0 0 0 1.7 -2.983l-8.423 -14.06a1.989 1.989 0 0 0 -3.4 0z"></path>
                        <path d="M12 9v4"></path>
                        <path d="M12 17h.01"></path>
                    </svg>`;
        const iconWarning = `<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-alert-triangle"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 9v4" /><path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /><path d="M12 16h.01" /></svg>`;
        if (type === "danger") {
            modalBody.innerHTML += iconError;
        }
        if (type === "success") {
            modalBody.innerHTML += iconSuccess;
        }
        if (type === "warning") {
            modalBody.innerHTML += iconWarning;
        }
        
        
        if (typeof content === 'string') {
            modalBody.innerHTML += "<p>" + content + "</p>";
        } else if (content instanceof HTMLElement) {
            modalBody.appendChild(modalTitle);
            modalBody.appendChild(content);
        }
        //modalContent.appendChild(modalCloseButton);
        modalContent.appendChild(modalStatus);
        modalContent.appendChild(modalBody);
        modalContent.appendChild(modalFooter);
        modalDialog.appendChild(modalContent);
        modal.appendChild(modalDialog);
        
        // Append the modal to the body
        document.body.appendChild(modal);
        
        // Initialize and show the modal using Bootstrap's modal plugin
        const resultModal = new bootstrap.Modal(modal, {
            keyboard: false
        });
        resultModal.show();
        
        // Redirect on close button click
        closeButton.addEventListener('click', function () {
            if (redirectUrl) {
                window.location.href = redirectUrl;
            } else {
                modal.remove();
            }
        });
        // Redirect on modal close
        modal.addEventListener('hidden.bs.modal', function () {
            if (redirectUrl) {
                window.location.href = redirectUrl;
            } else {
                modal.remove();
            }
        });
    },
    isMobileVue: false,
    ckEditor: {
        content: null,
        content_int: null
    },
    init: function () {
        // Router by page code in config.curPage
        if (undefined !== config.curPage && typeof App[config.curPage] === 'function') {
            console.log(config.curPage);
            App[config.curPage]();
        }
    },
    login: function () {
        // Gestion du toggle de l'affichage du mot de passe
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        const firstLoginMessage = document.getElementById('firstLoginMessage');
        const login = document.getElementById('username');

        // Inscription
        const modalCreateAccount = document.getElementById('modal-create-account');
        const btnOpenCreateAccount = document.getElementById('btn-open-create-account');
        btnOpenCreateAccount.addEventListener('click', function (e) {
            e.preventDefault();
            const modal = new bootstrap.Modal(modalCreateAccount);
            modal.show();
        });

        // Validation du formulaire d'inscription
        createAccountForm = document.getElementById('createAccountForm');
        createAccountForm.addEventListener('submit', function (e) {
            e.preventDefault();
            App.displayLoading("createAccountForm");
            const login = document.getElementById('login').value.trim();
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            console.log("Creating account for:", login, email);


            App.postData(
                "/ajax/createAccount",
                null,
                {
                    login: login,
                    email: email,
                    password: password
                }
            ).then((result) => {
                console.log(result);

                if (result.success) {
                    App.modal("success", "modal-create-account-success", "Inscription réussie", "Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.", "/login");   
                } else if (result.error) {
                    if (Array.isArray(result.error)) {
                        App.modal("danger", "modal-login-error", "Erreur de lors de l'inscription", result.error[0])
                    } else {
                        App.modal("danger", "modal-login-error", "Erreur de lors de l'inscription", result.error);
                    }
                }
            }).finally(function () {
                App.hideLoading("createAccountForm");
                
            });
        });
        
        
        // Éléments 2FA
        const twofaBlock = document.getElementById('twofa-block');
        const twofaCodeInput = document.getElementById('twofa-code');     // input hidden
        const twofaSubmitBtn = document.getElementById('twofa-submit');
        const twofaMessage = document.getElementById('twofa-message');
        const twofaMessageText = document.getElementById("twofa-message-text");
        const twofaQrCode = document.getElementById('twofa-qrcode');
        const twofaQrImg = document.getElementById('twofa-qrcode-img');
        const twofaDigitInputs = document.querySelectorAll('.twofa-digit'); // les 6 petites cases
        
        let twofaToken = null; // token temporaire renvoyé par le backend après /ajax/login
        let isTwofaSubmitting = false;
        
        
        function resetTwofa() {
            if (twofaBlock) {
                twofaBlock.classList.add("d-none");
            }
            if (twofaCodeInput) {
                twofaCodeInput.value = "";
            }
            if (twofaDigitInputs && twofaDigitInputs.length) {
                twofaDigitInputs.forEach(input => input.value = "");
            }
            if (twofaMessage && twofaMessageText) {
                twofaMessage.classList.add("d-none");
                twofaMessageText.textContent = "";
            }
            if (twofaQrCode) {
                twofaQrCode.classList.add("d-none");
            }
            if (twofaQrImg) {
                twofaQrImg.src = "";
            }
            twofaToken = null;
        }
        
        function showTwofaError(msg) {
            if (!twofaMessage || !twofaMessageText) return;
            twofaMessageText.textContent = msg || "";
            twofaMessage.classList.toggle("d-none", !msg);
        }
        
        function updateTwofaHidden() {
            if (!twofaCodeInput || !twofaDigitInputs.length) return "";
            const value = Array.from(twofaDigitInputs)
                .map(input => input.value || '')
                .join('');
            twofaCodeInput.value = value;
            return value;
        }
        
        function submitTwofa() {
            if (isTwofaSubmitting) {
                return;
            }
            
            showTwofaError("");
            
            if (!twofaToken) {
                showTwofaError("Session 2FA invalide. Merci de vous reconnecter");
                return;
            }
            
            const otp = (twofaCodeInput ? twofaCodeInput.value.trim() : "");
            
            if (!otp || otp.length !== 6) {
                showTwofaError("Merci de saisir un code 2FA valide (6 chiffres)");
                return;
            }
            
            isTwofaSubmitting = true;
            App.displayLoading("twofa-block");
            
            App.postData(
                "/ajax/verify2fa",
                null,
                {
                    twofaToken: twofaToken,
                    otp: otp   // côté PHP : $this->request->data("otp")
                }
            ).then((result) => {
                console.log(result);
                if (result.success) {
                    const redirectUrl = result.redirect || "/dashboard";
                    window.location.href = redirectUrl;
                } else if (result.error) {
                    showTwofaError(
                        typeof result.error === "string"
                            ? result.error
                            : "Erreur lors de la vérification"
                    );
                }
            }).finally(function () {
                isTwofaSubmitting = false;
                App.hideLoading("twofa-block");
            });
        }
        
        // On check bien que les éléments sont rendus dans le DOM
        if (togglePassword && passwordInput && eyeIcon) {
            // On met notre listener sur le click de l'icone
            togglePassword.addEventListener("click", function (e) {
                e.preventDefault();
                // Changer le type de l'input entre 'password' et 'text'
                const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
                passwordInput.setAttribute("type", type);
                // Changer l'icône SVG
                if (type === "text") {
                    eyeIcon.innerHTML = `
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M3 3l18 18"></path>
                    <path d="M10.477 10.477a2 2 0 0 0 2.831 2.83"></path>
                    <path d="M9.685 4.657a9 9 0 0 1 10.79 7.343m-.28 3.259c-.464 .878-1.02 1.709-1.652 2.472"></path>
                    <path d="M6.032 6.04a9 9 0 0 0-2.97 3.372m-.802 2.59c-.194 .34-.364 .687-.507 1.042"></path>
                `;
                } else {
                    eyeIcon.innerHTML = `
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                    <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                    <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"></path>
                `;
                }
            });
        }
        
        const formLogin = document.getElementById('login-form');
        if (formLogin) {
            formLogin.addEventListener('submit', function (e) {
                e.preventDefault();
                App.displayLoading("login-form");
                
                resetTwofa(); // on réinitialise l'état 2FA à chaque tentative de login
                
                const password = document.getElementById('password');
                
                App.postData(
                    "/ajax/login",
                    null,
                    {
                        login: login.value,
                        password: password.value,
                    }
                ).then((result) => {
                    console.log(result);
                    
                    if (result.success) {
                        // Cas 1 : pas de 2FA requise -> redirection immédiate
                        if (!result.requires_2fa) {
                            const redirectUrl = result.redirect || "/dashboard";
                            window.location.href = redirectUrl;
                            return;
                        }
                        
                        // Cas 2 : 2FA requise => on affiche le bloc 2FA
                        twofaToken = result.twofaToken || null;
                        
                        if (!twofaToken) {
                            App.modal("danger", "modal-login-error", "Erreur de connexion", "Token 2FA manquant");
                            return;
                        }
                        
                        if (twofaBlock) {
                            twofaBlock.classList.remove("d-none");
                        }
                        
                        // Si le backend renvoie un QR code inline (activation initiale)
                        if (result.qr_code && twofaQrImg && twofaQrCode) {
                            twofaQrImg.src = result.qr_code;
                            twofaQrCode.classList.remove("d-none");
                        }
                        
                        // focus sur la première case 2FA si présente
                        if (twofaDigitInputs && twofaDigitInputs.length) {
                            twofaDigitInputs[0].focus();
                            twofaDigitInputs[0].select();
                        }
                        
                    } else if (result.error) {
                        if (Array.isArray(result.error)) {
                            App.modal("danger", "modal-login-error", "Erreur de connexion", result.error[0])
                        } else {
                            App.modal("danger", "modal-login-error", "Erreur de connexion", result.error);
                        }
                    }
                }).finally(function () {
                    App.hideLoading("login-form");
                });
            });
        }
        
        // Vérification du code 2FA via bouton
        if (twofaSubmitBtn) {
            twofaSubmitBtn.addEventListener('click', function () {
                submitTwofa();
            });
        }
        
        // Gestion des 6 cases 2FA
        if (twofaDigitInputs && twofaDigitInputs.length === 6) {
            
            twofaDigitInputs.forEach((input, index) => {
                input.addEventListener('input', function () {
                    // On force un seul chiffre
                    this.value = this.value.replace(/\D/g, '').slice(0, 1);
                    
                    const currentValue = updateTwofaHidden();
                    
                    // Si un chiffre est saisi, focus sur la case suivante
                    if (this.value && index < twofaDigitInputs.length - 1) {
                        twofaDigitInputs[index + 1].focus();
                        twofaDigitInputs[index + 1].select();
                    }
                    
                    // Auto-submit si on a 6 chiffres
                    if (currentValue && currentValue.length === 6) {
                        submitTwofa();
                    }
                });
                
                input.addEventListener('keydown', function (e) {
                    // Backspace sur case vide : on revient à la précédente
                    if (e.key === 'Backspace' && !this.value && index > 0) {
                        twofaDigitInputs[index - 1].focus();
                        twofaDigitInputs[index - 1].select();
                    }
                    
                    if (e.key === 'ArrowLeft' && index > 0) {
                        e.preventDefault();
                        twofaDigitInputs[index - 1].focus();
                    }
                    
                    if (e.key === 'ArrowRight' && index < twofaDigitInputs.length - 1) {
                        e.preventDefault();
                        twofaDigitInputs[index + 1].focus();
                    }
                });
            });
            
            // Gestion du coller "123456" dans la première case
            twofaDigitInputs[0].addEventListener('paste', function (e) {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text') || '';
                const digits = paste.replace(/\D/g, '').slice(0, twofaDigitInputs.length).split('');
                
                digits.forEach((d, i) => {
                    twofaDigitInputs[i].value = d;
                });
                
                const currentValue = updateTwofaHidden();
                
                const nextIndex = digits.length < twofaDigitInputs.length
                    ? digits.length
                    : twofaDigitInputs.length - 1;
                
                twofaDigitInputs[nextIndex].focus();
                twofaDigitInputs[nextIndex].select();
                
                if (currentValue && currentValue.length === 6) {
                    submitTwofa();
                }
            });
        }
        
        const formForgetPassword = document.getElementById('form-forget-password');
        if (formForgetPassword) {
            formForgetPassword.addEventListener('submit', function (e) {
                e.preventDefault();
                App.displayLoading("form-forget-password");
                const email = document.getElementById('forget-email').value;
                App.postData(
                    "/ajax/forgotPassword", null,
                    {
                        email
                    }).then((result) => {
                    console.log(result);
                    if (result.success) {
                        App.modal("success", "modal-login-success", "Mail envoyé", result.message, "/login");
                    } else if (result.error) {
                        App.modal("danger", "modal-login-error", "Erreur de connexion", result.error);
                    }
                }).finally(function () {
                    App.hideLoading("form-forget-password");
                })
            });
        }
        
        if (firstLoginMessage && login) {
            function toggleFirstLoginMessage(value) {
                if (value === "adminsu") {
                    firstLoginMessage.classList.remove("d-none");
                } else {
                    firstLoginMessage.classList.add("d-none");
                }
            }
            
            // Écoute les modifications utilisateur
            login.addEventListener('input', function (e) {
                toggleFirstLoginMessage(e.target.value);
            });
            
            // Vérifie immédiatement la valeur au chargement (ex : auto-remplissage navigateur)
            toggleFirstLoginMessage(login.value);
        }
    },
    resetPassword: function () {
        // Gestion du toggle de l'affichage du mot de passe
        const togglePassword = document.getElementById('togglePassword');
        const toggleConfirm = document.getElementById('toggleConfirm');
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('confirm');
        const iconPassword = document.getElementById('iconPassword');
        const iconConfirm = document.getElementById('iconConfirm');
        // On check bien que les éléments sont rendus dans le DOM
        if (togglePassword && toggleConfirm && passwordInput && confirmInput && iconPassword && iconConfirm) {
            // On met notre listener sur le click de l'icone
            togglePassword.addEventListener("click", function (e) {
                e.preventDefault();
                // Changer le type de l'input entre 'password' et 'text'
                const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
                passwordInput.setAttribute("type", type);
                // Changer l'icône SVG
                if (type === "text") {
                    iconPassword.innerHTML = `
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M3 3l18 18"></path>
                        <path d="M10.477 10.477a2 2 0 0 0 2.831 2.83"></path>
                        <path d="M9.685 4.657a9 9 0 0 1 10.79 7.343m-.28 3.259c-.464 .878-1.02 1.709-1.652 2.472"></path>
                        <path d="M6.032 6.04a9 9 0 0 0-2.97 3.372m-.802 2.59c-.194 .34-.364 .687-.507 1.042"></path>
                    `;
                } else {
                    iconPassword.innerHTML = `
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                        <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"></path>
                    `;
                }
            });
            // On met notre listener sur le click de l'icone
            toggleConfirm.addEventListener("click", function (e) {
                e.preventDefault();
                // Changer le type de l'input entre 'password' et 'text'
                const type = confirmInput.getAttribute("type") === "password" ? "text" : "password";
                confirmInput.setAttribute("type", type);
                // Changer l'icône SVG
                if (type === "text") {
                    iconConfirm.innerHTML = `
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M3 3l18 18"></path>
                        <path d="M10.477 10.477a2 2 0 0 0 2.831 2.83"></path>
                        <path d="M9.685 4.657a9 9 0 0 1 10.79 7.343m-.28 3.259c-.464 .878-1.02 1.709-1.652 2.472"></path>
                        <path d="M6.032 6.04a9 9 0 0 0-2.97 3.372m-.802 2.59c-.194 .34-.364 .687-.507 1.042"></path>
                    `;
                } else {
                    iconConfirm.innerHTML = `
                        <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                        <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0"></path>
                        <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6"></path>
                    `;
                }
            });
            
            const resetForm = document.getElementById('reset-form');
            if (resetForm) {
                resetForm.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const user_id = document.getElementById('user_id').value;
                    const password = document.getElementById('password').value;
                    const confirm = document.getElementById('confirm').value;
                    const resetToken = document.getElementById('reset_token').value;
                    if (password !== confirm) {
                        App.modal("danger", "modal-login-error", "Erreur de connexion", "Les mots de passe ne sont pas identiques");
                        return;
                    }
                    App.displayLoading("reset-form");
                    App.postData(
                        "/ajax/resetPassword", null,
                        {
                            user_id,
                            password,
                            reset_token: resetToken
                        }).then((result) => {
                        if (result.success) {
                            App.modal("success", "modal-login-success", "Mail envoyé", result.message, "/login");
                        } else if (result.error) {
                            App.modal("danger", "modal-login-error", "Erreur de connexion", result.error);
                        }
                    }).finally(function () {
                        App.hideLoading("reset-form");
                    })
                });
            }
        }
    },
    dashboardAdmin: function () {
    },
    monitoring: function () {
        // table
        const list = new List('table-logs', {
            sortClass: 'table-sort',
            listClass: 'table-tbody',
            valueNames: [
                'sort-id',
                'sort-editor',
                'sort-user',
                'sort-date',
                'sort-ip',
                'sort-request',
                'sort-level',
                'sort-message',
                'sort-file',
                'sort-line',
                'sort-trace',
            ]
        });
    },
    manageAccounts: function () {
        let accountIdToDelete = null;  // Variable pour stocker l'ID du compte à supprimer
        // Fonction pour ajouter les écouteurs d'événements aux boutons de suppression
        function addDeleteButtonListeners() {
            const deleteButtons = document.querySelectorAll('.btn-outline-danger');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    accountIdToDelete = this.getAttribute('data-account-id');
                });
            });
        }
        
        // Ajouter les écouteurs d'événements initiaux
        addDeleteButtonListeners();
        
        let accountIdUpdate = null;  // Variable pour stocker l'ID du compte à modifier
        let accountLoginUpdate = null;  // Variable pour stocker le login du compte à modifier
        let accountEmailUpdate = null;  // Variable pour stocker le mail du compte à modifier
        let accountRoleUpdate = null;  // Variable pour stocker le role du compte à modifier
        
        // Fonction pour ajouter les écouteurs d'événements aux boutons de mise à jour
        function addUpdateButtonListeners() {
            const updateButtons = document.querySelectorAll('.btn-outline-warning');
            updateButtons.forEach(button => {
                button.addEventListener('click', function () {
                    accountIdUpdate = this.getAttribute('data-account-id');
                    accountLoginUpdate = this.getAttribute('data-login');
                    accountEmailUpdate = this.getAttribute('data-email');
                    accountRoleUpdate = this.getAttribute('data-role')
                    
                    // Mettez à jour les champs du modal de mise à jour avec les valeurs du compte sélectionné
                    document.getElementById('stl-login-update').value = accountLoginUpdate;
                    document.getElementById('stl-email-update').value = accountEmailUpdate;
                    
                    // Event pour implémenter le select avec le role actuel dans le modal de modification
                    const modalElement = document.getElementById('updateAccountModal');
                    modalElement.addEventListener('shown.bs.modal', function () {
                        const select = document.getElementById('stl-role-update');
                        for (let i = 0; i < select.options.length; i++) {
                            // selectionne l'option correspondante avec le role actuel
                            if (select.options[i].value === accountRoleUpdate) {
                                select.options[i].selected = true;
                            }
                        }
                    }, {once: true});
                    
                    
                    // Ouvrir le modal de mise à jour
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                });
            });
        }
        
        // Ajouter les écouteurs d'événements initiaux
        addUpdateButtonListeners();
        
        document.getElementById('createAccountForm').addEventListener('submit', function (event) {
            event.preventDefault();
            const role = document.getElementById('roleSelected').value;
            const login = document.getElementById('login').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            
            App.displayLoading("createAccountForm");
            // Effectuer l'appel AJAX pour créer le compte
            App.postData('/ajax/account/create', null, {login, email, password, role})
                .then((data) => {
                    if (data.success) {
                        // Sélectionner le tbody de la table des comptes
                        const tbody = document.querySelector(".table-vcenter.card-table tbody");
                        // Supprimer le message "Aucun compte" s'il existe
                        const noAccountsMessage = tbody.querySelector(".no-accounts-message");
                        if (noAccountsMessage) {
                            noAccountsMessage.remove();
                        }
                        // Créer une nouvelle ligne pour le compte ajouté
                        const row = document.createElement("tr");
                        row.id = "account-" + data.account.id;
                        row.innerHTML = `
                            <td class="account-login">${data.account.login}</td>
                            <td class="account-role text-azure">${data.account.role}</td>
                            <td class="account-email">${data.account.email}</td>
                            <td class="account-created-at">${data.account.createdAt}</td>
                            <td>
                                <div class="btn-list flex-nowrap">
                                                    <button type="button" class="btn btn-outline-warning btn-icon" data-account-id="${data.account.id}" data-login="${data.account.login}" data-email="${data.account.email}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-pencil">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"></path>
                                                            <path d="M4 20h4l10.5 -10.5a2.828 2.828 0 1 0 -4 -4l-10.5 10.5v4"></path>
                                                            <path d="M13.5 6.5l4 4"></path>
                                                        </svg>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger btn-icon" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal" data-account-id="${data.account.id}">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-trash">
                                                            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                                                            <path d="M4 7l16 0"/>
                                                            <path d="M10 11l0 6"/>
                                                            <path d="M14 11l0 6"/>
                                                            <path d="M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12"/>
                                                            <path d="M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                        `;
                        // Ajouter la ligne au tableau
                        tbody.appendChild(row);
                        // Ajoutez les écouteurs d'événements aux nouveaux boutons de suppression
                        addDeleteButtonListeners();
                        // Ajoutez les routeurs d'événements aux nouveaux boutons d'edition
                        addUpdateButtonListeners();
                        // Fermer le modal si le compte est créé avec succès
                        const modalElement = document.getElementById('createAccountModal');
                        if (modalElement) {
                            const modal = bootstrap.Modal.getInstance(modalElement);
                            modal.hide();
                            
                            // Supprimer explicitement l'overlay sombre si nécessaire
                            const backdrop = document.querySelector('.modal-backdrop');
                            if (backdrop) {
                                // Supprimer tous les backdrops restants
                                document.querySelectorAll('.modal-backdrop').forEach((backdrop) => {
                                    backdrop.remove();
                                });
                            }
                        }
                    } else {
                        let alertMessage = "";
                        if (data.error) {
                            // Vérifiez si result.error est un tableau avant d'utiliser join
                            if (Array.isArray(data.error)) {
                                alertMessage += data.error.join("<br>");
                            } else {
                                alertMessage += data.error;
                            }
                        }
                        alertMessage += '<button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                        // Affichage du modal d'erreur
                        App.modal("danger", "modal-message-result", "Enregistrement", alertMessage);
                    }
                })
                .finally(() => {
                    App.hideLoading("createAccountForm");
                });
        });
        
        // Confirmer la suppression
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        confirmDeleteBtn.addEventListener('click', function () {
            if (accountIdToDelete) {
                // Appel AJAX pour supprimer le compte
                App.postData('/ajax/account/delete', null, {account_id: accountIdToDelete})
                    .then((data) => {
                        if (data.success) {
                            // Supprime la ligne du compte dans le tableau
                            const accountRow = document.getElementById(`account-${accountIdToDelete}`);
                            if (accountRow) {
                                accountRow.remove();
                            }
                            // Sélectionner le tbody de la table des comptes
                            const tbody = document.querySelector(".table-vcenter.card-table tbody");
                            // Vérifier si le tbody est maintenant vide
                            if (tbody.querySelectorAll('tr').length === 0) {
                                // Ajoute un message indiquant qu'il n'y a aucun compte
                                const noAccountsMessage = document.createElement("tr");
                                noAccountsMessage.classList.add("no-accounts-message");
                                noAccountsMessage.innerHTML = `<td colspan="4" class="text-center fw-bold text-red">Aucun compte pour cet utilisateur !</td>`;
                                tbody.appendChild(noAccountsMessage);
                            }
                            // Ferme le modal
                            const modalElement = document.getElementById('confirmDeleteModal');
                            if (modalElement) {
                                const modal = bootstrap.Modal.getInstance(modalElement);
                                modal.hide();
                                
                                // TEST
                                const closeBtn = document.querySelector(
                                    '#confirmDeleteModal [data-bs-dismiss="modal"]'
                                );
                                if (closeBtn) {
                                    closeBtn.click();
                                }
                                // FIN TEST
                                
                                // Supprimer explicitement l'overlay sombre si nécessaire
                                const backdrop = document.querySelector('.modal-backdrop');
                                if (backdrop) {
                                    // Supprimer tous les backdrops restants
                                    document.querySelectorAll('.modal-backdrop').forEach((backdrop) => {
                                        backdrop.remove();
                                    });
                                }
                            }
                        } else {
                            let alertMessage = "";
                            if (data.error) {
                                // Vérifiez si result.error est un tableau avant d'utiliser join
                                if (Array.isArray(data.error)) {
                                    alertMessage += data.error.join("<br>");
                                } else {
                                    alertMessage += data.error;
                                }
                            }
                            alertMessage += '<button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                            // Affichage du modal d'erreur
                            App.modal("danger", "modal-message-result", "Enregistrement", alertMessage);
                        }
                    });
            }
        });
        
        // Confirmer la mise à jour
        const confirmUpdateBtn = document.getElementById('confirmUpdateBtn');
        confirmUpdateBtn.addEventListener('click', function () {
            if (accountIdUpdate) {
                const login = document.getElementById('stl-login-update').value;
                const role = document.getElementById('stl-role-update')?.value ?? null;
                const email = document.getElementById('stl-email-update').value;
                const password = document.getElementById('stl-password-update').value;
                // Appel AJAX pour mettre à jour le compte
                App.postData('/ajax/account/update', null, {account_id: accountIdUpdate, login, role, email, password})
                    .then((data) => {
                        if (data.success) {
                            console.log(data);
                            console.log(accountIdUpdate);
                            // Mise à jour de la ligne du compte dans le tableau
                            const accountRow = document.getElementById(`account-${accountIdUpdate}`);
                            if (accountRow) {
                                // Mets à jour les cellules de la ligne avec les nouvelles valeurs
                                accountRow.querySelector('.account-login').textContent = data.account.login;
                                accountRow.querySelector('.account-email').textContent = data.account.email;
                                accountRow.querySelector('.account-role').textContent = data.account.role;
                                
                                accountRow.querySelector('.btn-icon').setAttribute('data-login', data.account.login);
                                accountRow.querySelector('.btn-icon').setAttribute('data-email', data.account.email);
                                accountRow.querySelector('.btn-icon').setAttribute('data-role', data.account.role);
                            }
                            // Ferme le modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('updateAccountModal'));
                            modal.hide();
                        } else {
                            let alertMessage = "";
                            if (data.error) {
                                // Vérifiez si result.error est un tableau avant d'utiliser join
                                if (Array.isArray(data.error)) {
                                    alertMessage += data.error.join("<br>");
                                } else {
                                    alertMessage += data.error;
                                }
                            }
                            alertMessage += '<button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                            // Affichage du modal d'erreur
                            App.modal("danger", "modal-message-result", "Enregistrement", alertMessage);
                        }
                    });
            }
        });
        
        
    },
    manageAccount: function () {
        // Réinitialisation du mot de passe
        const confirmBtn = document.getElementById('changePasswordConfirm');
        confirmBtn.addEventListener('click', function () {
            const accountId = document.getElementById("accountId").value;
            const old_password = document.getElementById("old-password").value;
            const new_password = document.getElementById("new-password").value;
            App.displayLoading("form-edit-accounts-options");
            // Appel AJAX pour modifier le mdp du compte
            App.postData('/ajax/account/password/update', null, {account_id: accountId, old_password, new_password})
                .then((data) => {
                    if (data.success) {
                        let alertMessage = "Votre mot de passe a bien été modifié !";
                        alertMessage += '<button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                        // Affichage du modal d'erreur
                        App.modal("success", "modal-message-result", "Enregistrement", alertMessage, "/dashboard/account");
                    } else {
                        let alertMessage = "";
                        if (data.error) {
                            // Vérifiez si result.error est un tableau avant d'utiliser join
                            if (Array.isArray(data.error)) {
                                alertMessage += data.error.join("<br>");
                            } else {
                                alertMessage += data.error;
                            }
                        }
                        alertMessage += '<button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                        // Affichage du modal d'erreur
                        App.modal("danger", "modal-message-result", "Enregistrement", alertMessage);
                    }
                })
                .finally(() => {
                    App.hideLoading("form-edit-accounts-options");
                });
        });
    },
    manageBlockedAccounts: function () {
        
        let accountIdToUnlock = null;
        
        // Fonction pour ajouter les écouteurs d'événements aux boutons de mise à jour
        function addUpdateButtonListeners() {
            const updateButtons = document.querySelectorAll('.unlock-account');
            updateButtons.forEach(button => {
                button.addEventListener('click', function () {
                        const accountIdToUnlock = this.getAttribute('data-account-id');
                        unlockAccount(accountIdToUnlock)
                });
            });
        }
        
        // Ajouter les écouteurs d'événements initiaux
        addUpdateButtonListeners();
        
        // Confirmer le deverouuillage du compte
        function unlockAccount(accountIdToUnlock) {
                // Appel AJAX pour supprimer le compte
                App.postData('/ajax/account/unlock', null, {account_id: accountIdToUnlock})
                    .then((data) => {
                        if (data.success) {
                            // Supprime la ligne du compte dans le tableau
                            const accountRow = document.getElementById(`account-${accountIdToUnlock}`);
                            if (accountRow) {
                                accountRow.remove();
                            }
                            // Sélectionner le tbody de la table des comptes
                            const tbody = document.querySelector(".table-vcenter.card-table tbody");
                            // Vérifier si le tbody est maintenant vide
                            if (tbody.querySelectorAll('tr').length === 0) {
                                // Ajoute un message indiquant qu'il n'y a aucun compte
                                const noAccountsMessage = document.createElement("tr");
                                noAccountsMessage.classList.add("no-accounts-message");
                                noAccountsMessage.innerHTML = `<td colspan="4" class="text-center fw-bold">Aucun compte !</td>`;
                                tbody.appendChild(noAccountsMessage);
                            }

                        } else {
                            let alertMessage = "";
                            if (data.error) {
                                // Vérifiez si result.error est un tableau avant d'utiliser join
                                if (Array.isArray(data.error)) {
                                    alertMessage += data.error.join("<br>");
                                } else {
                                    alertMessage += data.error;
                                }
                            }
                            alertMessage += '<button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>';
                            // Affichage du modal d'erreur
                            App.modal("danger", "modal-message-result", "Enregistrement", alertMessage);
                        }
                    });
            }
        
    },
    postData: async function (url, headers, data) {
        // Default options are marked with *
        url = config.xdebug ? url + "" + config.xdebug : url;
        headers = headers || {"X-Requested-With": "XMLHttpRequest"};
        headers["X-CSRF-Token"] = config.csrfToken;
        data = data ?? {};

        if (data instanceof FormData) {
            if (!data.has("csrf_token")) {
                data.append("csrf_token", config.csrfToken);
            }
        } else if (typeof data === "object") {
            data = {...data, csrf_token: config.csrfToken};
        }
        // Si data est un objet FormData, ne pas ajouter le Content-Type
        // Laissez-le être défini automatiquement par le navigateur
        if (!(data instanceof FormData)) {
            headers["Content-Type"] = "application/json";
        }
        const response = await fetch(url, {
            method: "POST", // Methode HTTP
            mode: "cors", // no-cors, *cors, same-origin
            cache: "no-cache", // *default, no-cache, reload, force-cache, only-if-cached
            credentials: "same-origin", // include, *same-origin, omit
            headers: headers,
            redirect: "follow", // manual, *follow, error
            referrerPolicy: "no-referrer", // no-referrer, *no-referrer-when-downgrade, origin, origin-when-cross-origin, same-origin, strict-origin, strict-origin-when-cross-origin, unsafe-url
            body: data instanceof FormData ? data : JSON.stringify(data), // Si data est FormData, l'utiliser tel quel, sinon le transformer en JSON
        });
        const refreshedToken = response.headers.get("X-CSRF-Token");
        if (refreshedToken) {
            config.csrfToken = refreshedToken;
        }
        return response.json();
    },
    displayLoading: function (containerID) {
        this.hideLoading(containerID);
        const loader = document.createElement("div");
        loader.setAttribute("id", "loading");
        const container = document.getElementById(containerID);
        if (container) {
            container.setAttribute("data-loading", "true");
            container.appendChild(loader);
        }
    },
    hideLoading: function (containerID) {
        const container = document.getElementById(containerID);
        const loader = document.getElementById("loading");
        if (container) {
            container.setAttribute("data-loading", "false");
        }
        if (loader) {
            loader.remove();
        }
    },
    toggleTipsContainer: function (btnId, containerId) {
        const btn = document.getElementById(btnId);
        const container = document.getElementById(containerId);
        
        if (!btn || !container) {
            console.error("Button or container not found.");
            return;
        }
        
        btn.addEventListener("click", () => {
            container.style.display = container.style.display === 'none' ? '' : 'none';
        });
    }
};

function cookies() {
    
    // obtain cookieconsent plugin
    if (typeof initCookieConsent == "function") {
        let cc = initCookieConsent();
        let logo = '<img src="/assets/img/logo.png" style="width: 4em; height: 4em;">';
        let cookie = '🍪';
        
        cc.run({
            loging: false,
            force_consent: true,
            current_lang: 'fr',
            // theme_css: '/assets/css/cookieconsent.css',
            autoclear_cookies: true,                   // default: false
            cookie_name: 'vie_privee_projet',      // default: 'cc_cookie'
            cookie_domain: config.domain,
            cookie_expiration: 365,                    // default: 182
            page_scripts: true,                         // default: false
            hide_from_bots: true,
            revision: 1,
            gui_options: {
                consent_modal: {
                    layout: 'box',                      // box,cloud,bar
                    position: 'center center',           // bottom,middle,top + left,right,center
                    transition: 'slide'                 // zoom,slide
                },
                settings_modal: {
                    layout: 'bar',                      // box,bar
                    transition: 'slide'                 // zoom,slide
                }
            },
            
            onFirstAction: function (user_preferences, cookie) {
                // callback triggered only once
            },
            
            onAccept: function (cookie) {
                
                if (!cookie.level.includes("analytics")) {
                    window.dataLayer = window.dataLayer || [];
                    
                    function gtag() {
                        dataLayer.push(arguments);
                    }
                    
                    gtag('consent', 'default', {
                        'ad_storage': 'granted',
                        'analytics_storage': 'granted'
                    });
                }
            },
            
            onChange: function (cookie, changed_preferences) {
                
                // If analytics category's status was changed ...
                if (changed_preferences.indexOf('analytics') > -1) {
                    
                    // If analytics category is disabled ...
                    if (!cc.allowedCategory('analytics')) {
                        
                        window.dataLayer = window.dataLayer || [];
                        
                        function gtag() {
                            dataLayer.push(arguments);
                        }
                        
                        gtag('consent', 'default', {
                            'ad_storage': 'denied',
                            'analytics_storage': 'denied'
                        });
                    }
                }
                
            },
            
            languages: {
                'fr': {
                    consent_modal: {
                        title: '<span style="font-size:4em;">' + cookie + '</span>&nbsp;Nous utilisons des cookies !',
                        description: '<p>Nous utilisons des cookies à des fins de mesures d\'audience (Google Analytics) ainsi que pour mémoriser vos préférences.</p>                                  <p>Aucune donnée n\'est utilisée pour tracer votre activité ou partager des informations avec d\'autres sites.</p>' +
                            '<p class="text-right"><button type="button" data-cc="c-settings" class="cc-link">Préférences</button></p>',
                        primary_btn: {
                            text: '<i class="fa fa-check"></i> Accepter tout',
                            role: 'accept_all'              // 'accept_selected' or 'accept_all'
                        },
                        secondary_btn: {
                            text: '<i class="fas fa-times"></i> Refuser tout',
                            role: 'accept_necessary'        // 'settings' or 'accept_necessary'
                        },
                        revision_message: '<br><br> Notre politique de gestion des cookies a évoluée depuis votre dernière visite !'
                    },
                    settings_modal: {
                        title: '<h2>' + cookie + '&nbsp;Projet</h2>',
                        save_settings_btn: '<i class="fa fa-check-circle-o"></i> Ma sélection',
                        accept_all_btn: '<i class="fa fa-check"></i> Accepter tout',
                        reject_all_btn: '<i class="fas fa-times"></i> Refuser tout',
                        close_btn_label: 'Fermer',
                        cookie_table_headers: [
                            {col1: 'Nom'},
                            {col2: 'Domaine'},
                            {col3: 'Expiration'},
                            {col4: 'Description'}
                        ],
                        blocks: [
                            {
                                title: 'Vos préférences',
                                description: 'Nous utilisons des cookies pour vous assurer une expérience agréable et simple sur Comm\'une actu. Vous avez le choix d\'accepter ou pas certaines catégories de cookies non indispensables. Pour plus de détails sur les cookies et la gestion de la vie privée, consulter nos  <a href="/documents/mentions-legales" class="cc-link">mentions légales</a>.'
                            },
                            {
                                title: 'Cookies indispensables',
                                description: 'Cookies indispensables pour qu\'un site internet fonctionne convenablement. Pour avoir une session reconnue, pouvoir vous connecter et rester connecté au site par exemple.',
                                toggle: {
                                    value: 'necessary',
                                    enabled: true,
                                    readonly: true          // cookie categories with readonly=true are all treated as "necessary cookies"
                                },
                                cookie_table: [
                                    {
                                        col1: 'PHPSESSID',
                                        col2: config.domain,
                                        col3: '24h',
                                        col4: 'Identifiant unique de votre navigateur sur le serveur.',
                                        is_regex: true
                                    },
                                    {
                                        col1: 'vie_privee_projet',
                                        col2: config.domain,
                                        col3: '365 jours',
                                        col4: 'Mémorise vos préférences de cookies',
                                    }
                                ]
                            },
                            {
                                title: 'Cookies de performance et analyse',
                                description: 'Ces cookies nous permettent de mémoriser certains évènements lors de votre utilisation du site, permettant ainsi de dégager des statistiques d\'utilisation.',
                                toggle: {
                                    value: 'analytics',     // there are no default categories => you specify them
                                    enabled: false,
                                    readonly: false
                                },
                                cookie_table: [
                                    {
                                        col1: '_ga',
                                        col2: 'google.com',
                                        col3: '2 ans',
                                        col4: 'Google Analytics',
                                    },
                                    {
                                        col1: '_ga_TC9FJNZN57',
                                        col2: 'google.com',
                                        col3: '2 ans',
                                        col4: 'Google Analytics',
                                    }
                                ]
                            },
                        ]
                    }
                }
            }
        });
    } else {
        console.error("initCookieConsent is not loaded");
    }
}

function setAttributes(el, attrs) {
    for (var key in attrs) {
        el.setAttribute(key, attrs[key]);
    }
}

if (!String.prototype.includes) {
    String.prototype.includes = function (search, start) {
        'use strict';
        
        if (search instanceof RegExp) {
            throw TypeError('first argument must not be a RegExp');
        }
        if (start === undefined) {
            start = 0;
        }
        return this.indexOf(search, start) !== -1;
    };
}

function createIframe(id, content) {
    var iframe = document.createElement('iframe');
    iframe.style.width = "100%";
    iframe.style.height = "500px";
    iframe.frameBorder = "0";
    
    // Ensure the iframe is appended to the DOM before setting its content
    document.getElementById(id).appendChild(iframe);
    
    iframe.onload = function () {
        var iframeDoc = iframe.contentDocument || iframe.contentWindow.document;
        iframeDoc.open();
        iframeDoc.write(content);
        iframeDoc.close();
    };
}

/* Dropzone function */
function bytesToMB(bytes) {
    const bytesPerMB = 1024 * 1024; // 1 MB = 1024 * 1024 bytes
    return bytes / bytesPerMB;
}

function formatFileSize(bytes) {
    if (bytes >= 1073741824) {
        return (bytes / 1073741824).toLocaleString('fr-FR', {maximumFractionDigits: 2}) + ' Go';
    } else if (bytes >= 1048576) {
        return (bytes / 1048576).toLocaleString('fr-FR', {maximumFractionDigits: 2}) + ' Mo';
    } else if (bytes >= 1024) {
        return (bytes / 1024).toLocaleString('fr-FR', {maximumFractionDigits: 2}) + ' Ko';
    } else {
        return bytes.toLocaleString('fr-FR') + ' Octets';
    }
}