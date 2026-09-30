document.addEventListener("DOMContentLoaded",function(){const r=document.getElementById("tfa-start-setup"),b=document.getElementById("tfa-setup-modal"),d=document.getElementById("tfa-setup-loading"),u=document.getElementById("tfa-setup-content"),g=document.getElementById("tfa-qr-holder"),f=document.getElementById("tfa-secret-text"),s=document.getElementById("tfa-confirm-enable"),t=document.getElementById("tfa-confirm-code"),l=document.getElementById("tfa-copy-secret"),m=document.getElementById("tfa-copy-message"),p=document.getElementById("tfa-disable-form"),o=p?p.querySelector('button[type="submit"]'):null,h=document.getElementById("tfa-enable-block"),T=document.getElementById("tfa-disable-block"),v=document.getElementById("tfa-status-badge");let c=null;if(b&&typeof bootstrap<"u"&&(c=bootstrap.Modal.getOrCreateInstance(b)),!r&&!p)return;const M=document.querySelector('meta[name="csrf-token"]'),E=M?M.getAttribute("content"):"";function i(n,e){if(typeof toastr>"u"){alert(e);return}n==="success"?toastr.success(e):toastr.error(e)}async function w(n){return(n.headers.get("content-type")||"").includes("application/json")?await n.json():{success:!1,message:"Unexpected server response. Please try again."}}function B(){g&&(g.innerHTML=""),f&&(f.value=""),t&&(t.value="",t.classList.remove("is-invalid")),m&&(m.style.display="none"),l&&(l.disabled=!1,l.innerHTML='<i class="bi bi-clipboard"></i>'),d&&(d.style.display="none"),u&&(u.style.display="block"),s&&(s.disabled=!1,s.innerHTML=`
                <i class="bi bi-shield-check me-1"></i>
                Confirm &amp; Enable 2FA
            `)}r&&r.addEventListener("click",async function(){if(r.disabled)return;const n=r.innerHTML;r.disabled=!0,r.innerHTML=`
                    <span
                        class="spinner-border spinner-border-sm me-1"
                        role="status"
                        aria-hidden="true"
                    ></span>
                    Preparing...
                `,B(),d&&(d.style.display="block"),u&&(u.style.display="none"),c&&c.show();try{const e=await fetch(route("admin.settings.twoFa.setup"),{method:"GET",headers:{"X-CSRF-TOKEN":E,Accept:"application/json"}}),a=await w(e);if(!e.ok||!a.success){i("danger",a.message||"Could not start 2FA setup."),c&&c.hide();return}g&&(g.innerHTML=a.qr_code||""),f&&(f.value=a.secret||""),d&&(d.style.display="none"),u&&(u.style.display="block"),setTimeout(function(){t&&t.focus()},300)}catch(e){console.error("2FA setup error:",e),i("danger","Something went wrong. Please try again."),c&&c.hide()}finally{r.disabled=!1,r.innerHTML=n}}),s&&s.addEventListener("click",async function(){const n=t?t.value.trim():"";if(!/^\d{6}$/.test(n)){i("danger","Please enter a valid 6-digit authentication code."),t&&(t.classList.add("is-invalid"),t.focus());return}if(t&&t.classList.remove("is-invalid"),s.disabled)return;const e=s.innerHTML;s.disabled=!0,s.innerHTML=`
                    <span
                        class="spinner-border spinner-border-sm me-1"
                        role="status"
                        aria-hidden="true"
                    ></span>
                    Verifying...
                `;try{const a=await fetch(route("admin.settings.twoFa.enable"),{method:"POST",headers:{"X-CSRF-TOKEN":E,"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify({one_time_password:n})}),y=await w(a);if(!a.ok||!y.success){i("danger",y.message||"Invalid authentication code."),t&&(t.value="",t.focus());return}i("success",y.message||"Google 2FA enabled successfully."),v&&(v.innerHTML=`
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle me-1"></i>
                                Enabled
                            </span>
                        `),h&&(h.style.display="none"),T&&(T.style.display="block"),c&&c.hide()}catch(a){console.error("2FA enable error:",a),i("danger","Something went wrong. Please try again.")}finally{s.disabled=!1,s.innerHTML=e}}),l&&l.addEventListener("click",async function(){const n=f?f.value.trim():"";if(!n){i("danger","No secret key available.");return}if(!l.disabled)try{await navigator.clipboard.writeText(n),m&&(m.style.display="block",setTimeout(function(){m.style.display="none"},2e3)),l.innerHTML='<i class="bi bi-check-lg"></i>',setTimeout(function(){l.innerHTML='<i class="bi bi-clipboard"></i>'},2e3)}catch(e){console.error("Copy secret error:",e),i("danger","Unable to copy the secret key.")}}),t&&(t.addEventListener("input",function(){this.value=this.value.replace(/\D/g,"").slice(0,6),this.classList.remove("is-invalid")}),t.addEventListener("keydown",function(n){n.key==="Enter"&&!s.disabled&&(n.preventDefault(),s.click())})),b&&b.addEventListener("hidden.bs.modal",function(){B()}),p&&p.addEventListener("submit",async function(n){n.preventDefault();const e=p.querySelector('input[name="password"]');if(!e){i("danger","Password field not found.");return}const a=e.value.trim();if(!a){i("danger","Please enter your password."),e.focus();return}if(o&&o.disabled)return;const y=o?o.innerHTML:"";o&&(o.disabled=!0,o.innerHTML=`
                        ${y}
                        <span
                            class="spinner-border spinner-border-sm me-1"
                            role="status"
                            aria-hidden="true"
                        ></span>

                    `);try{const L=await fetch(route("admin.settings.twoFa.disable"),{method:"POST",headers:{"X-CSRF-TOKEN":E,"Content-Type":"application/json",Accept:"application/json"},body:JSON.stringify({password:a})}),k=await w(L);if(!L.ok||!k.success){i("danger",k.message||"Incorrect password."),e.value="",e.focus();return}i("success",k.message||"Google 2FA disabled successfully."),v&&(v.innerHTML=`
                            <span class="badge bg-secondary">
                                <i class="bi bi-shield-x me-1"></i>
                                Disabled
                            </span>
                        `),T&&(T.style.display="none"),h&&(h.style.display="block"),e.value=""}catch(L){console.error("2FA disable error:",L),i("danger","Unable to disable Google 2FA right now. Please try again.")}finally{o&&(o.disabled=!1,o.innerHTML=y)}})});
