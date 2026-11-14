document.addEventListener('submit', async (e) => {
  const form = e.target.closest('form[data-toggle-form]');
  if (!form) return;
  e.preventDefault();

  const btn = form.querySelector('[data-toggle-btn]');
  const habitKeySel = 'habit-' + form.querySelector('input[name="habit_id"]').value;
  const tanggal = form.querySelector('input[name="tanggal"]')?.value ?? e.submitter?.value;

  try {
    const res = await fetch(form.action, {
      method: 'POST',
      body: new FormData(form),
      headers: { 'X-Requested-With': 'fetch' },
    });
    if (!res.ok) throw new Error('gagal');
    const data = await res.json();
    if (data.error) throw new Error(data.error);

    if (btn) {
      btn.textContent = data.done ? 'Batalkan' : 'Tandai selesai';
      btn.classList.toggle('btn-success', data.done);
      btn.classList.toggle('btn-primary', !data.done);
    }

    document.querySelectorAll(`[data-hari="${habitKeySel}"][data-tanggal="${tanggal}"]`).forEach((el) => {
      el.classList.toggle('bg-success', data.done);
      el.classList.toggle('bg-base-300', !data.done && el.hasAttribute('data-dot'));
      el.classList.toggle('bg-base-200', !data.done && !el.hasAttribute('data-dot'));
    });

    const streakBadge = document.querySelector(`[data-streak-badge="${habitKeySel}"]`);
    if (streakBadge && data.streak !== null) {
      streakBadge.textContent = `🔥 ${data.streak} hari`;
    }

    const total = document.getElementById('total-hari-ini');
    if (total && data.total_today !== undefined) {
      total.textContent = `${data.total_today}/${total.dataset.total} selesai`;
    }
  } catch {
    form.submit();
  }
});
