<!-- Toast System (HTML + CSS + JS) -->
<ul class="notifications"></ul>

<style>
.notifications {
  position: fixed;
  top: 20px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 9999;
  display: flex;
  flex-direction: column;
  gap: 12px;
  align-items: center;
}


.notifications .toast {
  display: flex;
  align-items: center;
  width: 360px;
  padding: 14px 18px;
  border-radius: 10px;
  background: linear-gradient(135deg, #ffffff, #f0f0f0);
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
  animation: slideIn 0.4s ease forwards;
  position: relative;
  overflow: hidden;
  border-left: 6px solid;
  transition: all 0.3s ease;
  flex-basis: auto;
}

.toast.success {
  border-color: #22c55e;
}

.toast.error {
  border-color: #ef4444;
}

.toast.hide {
  animation: slideOut 0.4s ease forwards;
}

.toast::before {
  content: "";
  position: absolute;
  bottom: 0;
  left: 0;
  height: 4px;
  width: 100%;
  background: currentColor;
  animation: progressBar 5s linear forwards;
  opacity: 0.8;
}

.toast.success::before {
  color: #22c55e;
}

.toast.error::before {
  color: #ef4444;
}

@keyframes slideIn {
  0% { transform: translateY(-120%); opacity: 0; }
  100% { transform: translateY(0); opacity: 1; }
}


@keyframes slideOut {
  0% { transform: translateY(0); opacity: 1; }
  100% { transform: translateY(-120%); opacity: 0; }
}


@keyframes progressBar {
  100% { width: 0%; }
}

.toast .column {
  display: flex;
  align-items: center;
  gap: 12px;
  flex-grow: 1;
}

.toast .column i {
  font-size: 1.5rem;
}

.toast.success .column i {
  color: #22c55e;
}

.toast.error .column i {
  color: #ef4444;
}

.toast .column span {
  font-size: 1rem;
  color: #333;
  font-weight: 500;
}

.toast i.close {
  font-size: 1.1rem;
  color: #999;
  cursor: pointer;
  transition: color 0.2s ease;
}

.toast i.close:hover {
  color: #111;
}
</style>


<!-- Toast JavaScript -->
<script>
const notifications = document.querySelector(".notifications");

const toastDetails = {
    timer: 3000,
    success: { icon: 'fa-solid fa-circle-check', text: 'Success!' },
    error: { icon: 'fa-solid fa-circle-xmark', text: 'Something went wrong.' }
};

const removeToast = (toast) => {
    toast.classList.add("hide");
    setTimeout(() => toast.remove(), 500);
};

const createToast = (type, customMessage = null) => {
    const { icon, text } = toastDetails[type];
    const toast = document.createElement("li");
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <div class="column">
            <i class="${icon}"></i>
            <span>${customMessage || text}</span>
        </div>
        <i class="fa-solid fa-xmark" onclick="removeToast(this.parentElement)"></i>
    `;
    notifications.appendChild(toast);
    setTimeout(() => removeToast(toast), toastDetails.timer);
};
</script>
