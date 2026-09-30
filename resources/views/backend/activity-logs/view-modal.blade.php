<div
    class="modal fade"
    id="activity-log-modal"
    tabindex="-1"
    aria-labelledby="activityLogModalLabel"
    aria-hidden="true"
    data-bs-backdrop="static"
    data-bs-keyboard="false"
>
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h5
                    class="modal-title fw-bold"
                    id="activityLogModalLabel"
                >
                    <i class="bi bi-clock-history me-2"></i>
                    Activity Log Details
                </h5>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                ></button>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
                <!-- Loader -->
                <div
                    id="activity-log-loader"
                    class="text-center py-5 d-none"
                >
                    <div
                        class="spinner-border text-primary"
                        role="status"
                    >
                        <span class="visually-hidden">
                            Loading...
                        </span>
                    </div>
                    <div class="mt-2 text-muted small">
                        Loading activity details...
                    </div>
                </div>
                <!-- Content -->
                <div id="activity-log-content">
                    <div class="row g-4">
                        <!-- Log Name -->
                        <div class="col-md-6">
                            <div class="fw-semibold mb-1">
                                Log Name
                            </div>
                            <div
                                id="log-name"
                                class="text-muted"
                            >
                                N/A
                            </div>
                        </div>
                        <!-- User -->
                        <div class="col-md-6">
                            <div class="fw-semibold mb-1">
                                User
                            </div>
                            <div
                                id="log-user"
                                class="text-muted"
                            >
                                N/A
                            </div>
                        </div>
                        <!-- Event -->
                        {{-- <div class="col-md-6">
                            <div class="fw-semibold mb-1">
                                Event
                            </div>
                            <div
                                id="log-event"
                                class="text-muted"
                            >
                                N/A
                            </div>
                        </div> --}}
                        <!-- Date -->
                        <div class="col-md-6">
                            <div class="fw-semibold mb-1">
                                Date
                            </div>
                            <div
                                id="log-date"
                                class="text-muted"
                            >
                                N/A
                            </div>
                        </div>
                        <!-- Description -->
                        <div class="col-12">
                            <div class="fw-semibold mb-1">
                                Description
                            </div>
                            <div
                                id="log-description"
                                class="text-muted"
                            >
                                N/A
                            </div>
                        </div>
                        <!-- Properties -->
                        <div class="col-12">
                            <div class="fw-semibold mb-2">
                                Properties
                            </div>
                            <pre
                                id="log-properties"
                                class="border rounded p-3 mb-0"
                                style="
                                    max-height:350px;
                                    overflow:auto;
                                    white-space:pre-wrap;
                                    word-break:break-word;
                                "
                            >{}</pre>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal Footer -->
            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
