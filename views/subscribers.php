
<div id="alertx" class="main wrap">
    <?php include_once ALERTX_PATH .'/views/global/header.php'; ?>

    <section class="page" id="page-subscribers">
        <div class="page-header">
            <div>
                <div class="page-eyebrow">People</div>
                <div class="page-h1">Subscribers</div>
                <div class="page-desc">Everyone who asked to be told when a product comes back.</div>
            </div>
            <div class="page-actions">
                <button class="btn btn-secondary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <path d="M7 10l5 5 5-5" />
                        <path d="M12 15V3" />
                    </svg> Export CSV </button>
            </div>
        </div>
        <section class="mini-stat-row">
            <div class="mini-stat">
                <div class="val">12,847</div>
                <div class="lbl">Total subscribers</div>
            </div>
            <div class="mini-stat">
                <div class="val accent">614</div>
                <div class="lbl">New this week</div>
            </div>
            <div class="mini-stat">
                <div class="val warn">2,930</div>
                <div class="lbl">Pending restock</div>
            </div>
            <div class="mini-stat">
                <div class="val" style="color:var(--success)">1,742</div>
                <div class="lbl">Converted to sale</div>
            </div>
        </section>
        <section class="section">
            <div class="filter-bar" style="justify-content:space-between;">
                <div class="seg-tabs">
                    <span class="seg-tab active">All</span>
                    <span class="seg-tab">Pending</span>
                    <span class="seg-tab">Notified</span>
                    <span class="seg-tab">Converted</span>
                </div>
                <div style="display:flex;gap:10px;align-items:center;">
                    <div class="search-inline">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="7" />
                            <path d="M21 21l-4.3-4.3" />
                        </svg>
                        <input type="text" placeholder="Search email or product">
                    </div>
                    <div class="filter-select">Product: All <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                            <path d="M6 9l6 6 6-6" />
                        </svg>
                    </div>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th class="cell-check">
                            <input type="checkbox">
                        </th>
                        <th>Subscriber</th>
                        <th>Product</th>
                        <th>Channel</th>
                        <th>Subscribed</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="cell-check">
                            <input type="checkbox">
                        </td>
                        <td>t***a@gmail.com</td>
                        <td>
                            <div class="cell-product">iPhone 15 Pro Case</div>
                            <div class="cell-sub">All variations</div>
                        </td>
                        <td>
                            <span class="channel-tag">
                                <span class="channel-dot" style="background:var(--alertx-violet)"></span>Email </span>
                        </td>
                        <td>2 hours ago</td>
                        <td>
                            <span class="status-pill pending">Pending</span>
                        </td>
                        <td>
                            <div class="row-actions">
                                <button title="Resend">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 4v6h6" />
                                        <path d="M3.5 15a9 9 0 1 0 2-9.5L1 10" />
                                    </svg>
                                </button>
                                <button title="Remove">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18" />
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="cell-check">
                            <input type="checkbox">
                        </td>
                        <td>r***n@yahoo.com</td>
                        <td>
                            <div class="cell-product">Vintage Denim Jacket</div>
                            <div class="cell-sub">Black / XL</div>
                        </td>
                        <td>
                            <span class="channel-tag">
                                <span class="channel-dot" style="background:var(--alertx-orange)"></span>WhatsApp </span>
                        </td>
                        <td>5 hours ago</td>
                        <td>
                            <span class="status-pill notified">Notified</span>
                        </td>
                        <td>
                            <div class="row-actions">
                                <button title="Resend">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 4v6h6" />
                                        <path d="M3.5 15a9 9 0 1 0 2-9.5L1 10" />
                                    </svg>
                                </button>
                                <button title="Remove">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18" />
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="cell-check">
                            <input type="checkbox">
                        </td>
                        <td>m***h@outlook.com</td>
                        <td>
                            <div class="cell-product">Wireless Earbuds Pro</div>
                            <div class="cell-sub">Matte Black</div>
                        </td>
                        <td>
                            <span class="channel-tag">
                                <span class="channel-dot" style="background:var(--alertx-violet)"></span>Email </span>
                        </td>
                        <td>Yesterday</td>
                        <td>
                            <span class="status-pill converted">Converted</span>
                        </td>
                        <td>
                            <div class="row-actions">
                                <button title="Resend">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 4v6h6" />
                                        <path d="M3.5 15a9 9 0 1 0 2-9.5L1 10" />
                                    </svg>
                                </button>
                                <button title="Remove">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18" />
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="cell-check">
                            <input type="checkbox">
                        </td>
                        <td>f***a@gmail.com</td>
                        <td>
                            <div class="cell-product">Ceramic Pour-Over Set</div>
                            <div class="cell-sub">All variations</div>
                        </td>
                        <td>
                            <span class="channel-tag">
                                <span class="channel-dot" style="background:var(--alertx-violet)"></span>Email </span>
                        </td>
                        <td>Yesterday</td>
                        <td>
                            <span class="status-pill pending">Pending</span>
                        </td>
                        <td>
                            <div class="row-actions">
                                <button title="Resend">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 4v6h6" />
                                        <path d="M3.5 15a9 9 0 1 0 2-9.5L1 10" />
                                    </svg>
                                </button>
                                <button title="Remove">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18" />
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="cell-check">
                            <input type="checkbox">
                        </td>
                        <td>k***m@gmail.com</td>
                        <td>
                            <div class="cell-product">Vintage Denim Jacket</div>
                            <div class="cell-sub">Red / L</div>
                        </td>
                        <td>
                            <span class="channel-tag">
                                <span class="channel-dot" style="background:var(--alertx-magenta)"></span>SMS </span>
                        </td>
                        <td>2 days ago</td>
                        <td>
                            <span class="status-pill notified">Notified</span>
                        </td>
                        <td>
                            <div class="row-actions">
                                <button title="Resend">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M1 4v6h6" />
                                        <path d="M3.5 15a9 9 0 1 0 2-9.5L1 10" />
                                    </svg>
                                </button>
                                <button title="Remove">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M3 6h18" />
                                        <path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div class="table-footer">
                <span style="font-size:12px;color:var(--ink-soft)">Showing 5 of 12,847 subscribers</span>
                <div class="pager">
                    <button class="active">1</button>
                    <button>2</button>
                    <button>3</button>
                    <button>…</button>
                </div>
            </div>
        </section>
    </section>
</div>