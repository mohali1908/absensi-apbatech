// const leaveDetailModal = new bootstrap.Modal("#leave-detail-modal");
// Ambil elemen modal dengan ID 'leave-detail-modal'
const leaveDetailModal = document.getElementById(
    "leave-detail-modal"
);


// Tambahkan event listener ketika modal akan ditampilkan
leaveDetailModal.addEventListener("show.bs.modal", async (event) => {
     // Ambil elemen yang memicu modal (tombol)
    const badgeTrigger = event.relatedTarget;

    // Ambil leaveId dari atribut data-leave-id
    const { leaveId } = badgeTrigger.dataset;

    // Lakukan fetch ke API untuk mengambil data leave (cuti) berdasarkan leaveId
    const res = await fetch(leaveUrl + "?id=" + leaveId);

    // Ubah respon ke dalam format JSON
    const data = await res.json();

    // Ambil elemen-elemen dalam modal yang akan diisi dengan data
    const leaveReasonModal = leaveDetailModal.querySelector("#leave-reason");
    const leaveStartDateModal = leaveDetailModal.querySelector("#leave-start-date");
    const leaveEndDateModal = leaveDetailModal.querySelector("#leave-end-date");

    
     // Isi elemen-elemen modal dengan data yang diambil dari API
     leaveReasonModal.textContent = data.reason;
     leaveStartDateModal.textContent = data.start_date;
     leaveEndDateModal.textContent = data.end_date;
});
