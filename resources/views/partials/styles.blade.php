<style>
/* ============================================================
   BluePeak Fintech - Shared Stylesheet
   ============================================================ */
:root{
  --primary:#0c2053;
  --primary-dark:#081638;
  --primary-light:#e8f1fc;
  --accent:#0b5cc5;
  --bg:#f3f5f8;
  --card:#ffffff;
  --text:#1e2733;
  --muted:#6b7684;
  --border:#e3e7ee;
  --green:#188a4c;
  --green-bg:#e5f6ec;
  --orange:#b76e00;
  --orange-bg:#fff3e0;
  --red:#c62828;
  --red-bg:#fdeaea;
  --blue-bg:#e7f0fd;
  --radius:14px;
  --shadow:0 2px 10px rgba(15,35,65,.06);
}
*{box-sizing:border-box;}
html,body{overflow-x:hidden; max-width:100%;}
/* Bootstrap's CDN can be slow or blocked; this app leans on .d-none for
   real show/hide logic (not just spacing), so it needs to work even if
   Bootstrap's stylesheet never loads. */
.d-none{display:none !important;}
body{
  background:var(--bg);
  color:var(--text);
  font-family:'Segoe UI',Roboto,Arial,sans-serif;
}
a{text-decoration:none;}

.card-flat{background:var(--card); border:1px solid var(--border); border-radius:var(--radius); box-shadow:var(--shadow);}
.text-muted-fin{color:var(--muted) !important;}
.rupee::before{content:"₹";}
.brand-text{color:var(--primary);}
.btn-primary-fin{background:var(--primary); border-color:var(--primary); color:#fff;}
.btn-primary-fin:hover{background:var(--primary-dark); border-color:var(--primary-dark); color:#fff;}
.btn-outline-fin{border:1px solid var(--primary); color:var(--primary); background:transparent;}
.btn-outline-fin:hover{background:var(--primary); color:#fff;}
.section-title{font-weight:700; color:var(--primary); margin-bottom:2px;}
.page-sub{color:var(--muted); font-size:.85rem;}

.badge-status{padding:.32em .7em; border-radius:20px; font-size:.72rem; font-weight:600; display:inline-flex; align-items:center; gap:5px; white-space:nowrap;}
.badge-success{background:var(--green-bg); color:var(--green);}
.badge-warning{background:var(--orange-bg); color:var(--orange);}
.badge-danger{background:var(--red-bg); color:var(--red);}
.badge-info{background:var(--blue-bg); color:var(--accent);}
.badge-secondary{background:var(--border); color:var(--muted);}

#loginScreen{min-height:100vh; display:flex; align-items:center; justify-content:center; background:linear-gradient(160deg,var(--accent) 0%,#123a7a 45%,var(--primary) 100%); padding:20px;}
.login-box{width:100%; max-width:420px; background:var(--card); border-radius:18px; box-shadow:0 20px 60px rgba(0,0,0,.35); padding:32px 26px;}
.login-logo{width:76px;height:76px;border-radius:20px;background:#fff;box-shadow:0 4px 14px rgba(0,0,0,.12); display:flex;align-items:center;justify-content:center;margin:0 auto 10px;padding:8px;}
.login-logo img{width:100%;height:100%;object-fit:contain;}
.otp-box input{text-align:center; font-size:1.1rem; letter-spacing:2px;}

.app-shell{display:flex; min-height:100vh;}
.sidebar{width:250px; background:var(--primary); color:#fff; flex-shrink:0; position:fixed; top:0; left:0; bottom:0; z-index:1030; overflow-y:auto; transition:transform .25s ease;}
.sidebar .brand{display:flex;align-items:center;gap:10px;padding:18px 16px;border-bottom:1px solid rgba(255,255,255,.12);}
.sidebar .brand .logo-sm{width:36px;height:36px;border-radius:9px;background:#fff;display:flex;align-items:center;justify-content:center;padding:4px;}
.sidebar .brand .logo-sm img{width:100%;height:100%;object-fit:contain;}
.sidebar nav{padding:10px 0;}
.sidebar .nav-link{color:rgba(255,255,255,.82); padding:11px 20px; font-size:.92rem; border-left:3px solid transparent; display:flex; align-items:center; gap:12px; cursor:pointer; transition:.15s;}
.sidebar .nav-link i{width:18px; text-align:center;}
.sidebar .nav-link:hover{background:rgba(255,255,255,.08); color:#fff;}
.sidebar .nav-link.active{background:rgba(255,255,255,.14); color:#fff; border-left-color:#fff; font-weight:600;}
.main-wrap{flex:1; margin-left:250px; min-width:0; display:flex; flex-direction:column;}
.topbar{background:var(--card); border-bottom:1px solid var(--border); padding:12px 18px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:1020;}
.content-area{padding:18px; flex:1;}
.sidebar-overlay{display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:1025;}
@media (max-width:991.98px){
  .sidebar{transform:translateX(-100%);}
  .sidebar.show{transform:translateX(0);}
  .main-wrap{margin-left:0;}
  .sidebar-overlay.show{display:block;}
}

.stat-card{background:var(--card); border:1px solid var(--border); border-radius:var(--radius); padding:16px; box-shadow:var(--shadow); height:100%;}
.stat-icon{width:42px;height:42px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.stat-value{font-size:1.35rem; font-weight:700;}
.stat-label{font-size:.76rem; color:var(--muted);}

.table-fin thead th{font-size:.75rem; text-transform:uppercase; color:var(--muted); border-bottom:2px solid var(--border); font-weight:700;}
.table-fin td{vertical-align:middle; font-size:.87rem;}
.data-cards{display:none;}
@media (max-width:767.98px){
  .table-responsive-fin{display:none;}
  .data-cards{display:block;}
}
.data-card{background:var(--card); border:1px solid var(--border); border-radius:12px; padding:14px; margin-bottom:12px; box-shadow:var(--shadow);}
.data-card .dc-row{display:flex; justify-content:space-between; font-size:.83rem; padding:3px 0; border-bottom:1px dashed var(--border);}
.data-card .dc-row:last-child{border-bottom:none;}
.data-card .dc-label{color:var(--muted);}
.data-card .dc-head{display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;}
.dc-row{display:flex; justify-content:space-between; font-size:.87rem; padding:5px 0; border-bottom:1px dashed var(--border);}

#customerApp{background:var(--bg); min-height:100vh; padding-bottom:calc(76px + env(safe-area-inset-bottom));}
.cust-header{background:linear-gradient(120deg,var(--primary),var(--accent)); color:#fff; padding:calc(16px + env(safe-area-inset-top)) 16px 30px; border-radius:0 0 24px 24px;}
.cust-header-top{display:flex; justify-content:space-between; align-items:flex-start;}
.cust-greeting{font-size:.78rem; opacity:.82;}
.cust-username{font-weight:700; font-size:1.15rem; margin-top:1px;}
.cust-header-actions{display:flex; align-items:center; gap:8px; flex-shrink:0;}
.cust-header-page{display:flex; justify-content:space-between; align-items:center; margin-top:18px; gap:10px;}
.cust-page-title{font-weight:700; font-size:1.08rem; color:#fff; min-width:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;}
.icon-btn{position:relative; width:38px; height:38px; min-width:38px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; background:rgba(255,255,255,.16); color:#fff; text-decoration:none; font-size:.98rem; border:none; padding:0; -webkit-tap-highlight-color:transparent; transition:background .15s, transform .1s;}
.icon-btn:active{background:rgba(255,255,255,.32); transform:scale(.94);}
.icon-btn.icon-btn-muted{color:#fff; opacity:.9;}
.icon-btn .icon-badge{position:absolute; top:-3px; right:-3px; background:var(--red); color:#fff; font-size:.6rem; font-weight:700; min-width:16px; height:16px; border-radius:8px; display:flex; align-items:center; justify-content:center; padding:0 3px; border:2px solid var(--primary);}
.cust-content{margin-top:-20px; padding:0 14px;}
.bottom-nav{position:fixed; left:0; right:0; bottom:0; background:rgba(255,255,255,.94); backdrop-filter:blur(14px); -webkit-backdrop-filter:blur(14px); border-top:1px solid var(--border); display:flex; z-index:1040; box-shadow:0 -4px 18px rgba(15,35,65,.08); padding-bottom:env(safe-area-inset-bottom);}
.bottom-nav .bn-item{flex:1; text-align:center; padding:9px 2px 8px; color:var(--muted); font-size:.66rem; text-decoration:none; border-top:3px solid transparent; -webkit-tap-highlight-color:transparent;}
.bottom-nav .bn-item i{display:block; font-size:1.08rem; margin-bottom:3px;}
.bottom-nav .bn-item.active{color:var(--primary); border-top-color:var(--primary); font-weight:600;}
.progress-fin{height:10px; border-radius:6px; background:var(--border);}
.qtile{background:var(--card); border:1px solid var(--border); border-radius:14px; padding:12px 6px; text-align:center; box-shadow:var(--shadow); display:block; color:inherit; -webkit-tap-highlight-color:transparent; transition:transform .1s;}
.qtile:active{transform:scale(.96);}
.qtile i{font-size:1.2rem; color:var(--primary);}
.qtile span{display:block; font-size:.7rem; margin-top:4px; color:var(--text);}
.doc-card{background:var(--card); border:1px solid var(--border); border-radius:14px; padding:16px; margin-bottom:12px; box-shadow:var(--shadow);}

.filters-overlay{position:fixed; inset:0; background:var(--card); z-index:2000; display:flex; flex-direction:column; transform:translateY(100%); visibility:hidden; transition:transform .3s cubic-bezier(.32,.72,0,1), visibility 0s .3s;}
.filters-overlay.open{transform:translateY(0); visibility:visible; transition:transform .3s cubic-bezier(.32,.72,0,1), visibility 0s;}
.filters-header{display:flex; justify-content:space-between; align-items:center; padding:calc(14px + env(safe-area-inset-top)) 16px 14px; border-bottom:1px solid var(--border); flex-shrink:0;}
.filters-header .fw-bold{font-size:1.05rem; color:var(--text);}
.filters-body{flex:1; display:flex; overflow:hidden;}
.filters-nav{width:36%; background:var(--bg); overflow-y:auto; flex-shrink:0;}
.filters-nav-item{padding:16px 10px; font-size:.82rem; color:var(--muted); border-bottom:1px solid var(--border); cursor:pointer; border-left:3px solid transparent; -webkit-tap-highlight-color:transparent;}
.filters-nav-item.active{background:var(--card); color:var(--primary); font-weight:700; border-left-color:var(--primary);}
.filters-options{flex:1; overflow-y:auto; padding:4px 16px;}
.filter-panel.d-none{display:none !important;}
.filter-option{position:relative; display:flex; align-items:center; gap:10px; padding:14px 2px; border-bottom:1px solid var(--border); cursor:pointer; margin:0; -webkit-tap-highlight-color:transparent;}
.filter-option input{position:absolute; opacity:0; width:0; height:0;}
.filter-option .filter-check{width:14px; color:var(--muted); opacity:.25; flex-shrink:0;}
.filter-option input:checked ~ .filter-check{opacity:1; color:var(--primary);}
.filter-option .filter-label{font-size:.88rem;}
.filters-footer{display:flex; border-top:1px solid var(--border); flex-shrink:0; padding-bottom:env(safe-area-inset-bottom);}
.filters-footer button{flex:1; padding:15px; border:none; background:none; font-weight:600; font-size:.9rem; -webkit-tap-highlight-color:transparent;}
.filters-footer .btn-close-filters{color:var(--muted); border-right:1px solid var(--border);}
.filters-footer .btn-apply-filters{color:var(--primary);}

.fav-toggle-btn{position:absolute; top:8px; right:8px; width:30px; height:30px; border-radius:50%; background:rgba(255,255,255,.92); border:none; display:flex; align-items:center; justify-content:center; box-shadow:0 2px 8px rgba(15,35,65,.18); color:var(--muted); font-size:.82rem; padding:0; -webkit-tap-highlight-color:transparent; transition:transform .1s;}
.fav-toggle-btn:active{transform:scale(.9);}
.fav-toggle-btn i.text-danger{color:#e5484d;}
.product-card{position:relative; background:var(--card); border:1px solid var(--border); border-radius:16px; overflow:hidden; box-shadow:var(--shadow);}
.product-card img, .product-card .product-thumb-placeholder{display:block;}

.copy-chip{cursor:pointer; border:1px dashed var(--border); border-radius:8px; padding:6px 10px; display:flex; justify-content:space-between; align-items:center; font-size:.85rem;}
.qr-box{width:150px;height:150px;background:repeating-linear-gradient(45deg,#333,#333 6px,#fff 6px,#fff 12px); border:6px solid #fff; border-radius:8px; margin:0 auto;}
.qr-box img{width:100%;height:100%;object-fit:contain;background:#fff;}
.screenshot-thumb{width:52px;height:52px;border-radius:8px;object-fit:cover;border:1px solid var(--border); background:var(--primary-light);}
.form-section-title{font-weight:700; color:var(--primary); border-bottom:2px solid var(--primary-light); padding-bottom:6px; margin:22px 0 14px;}
.form-section-title:first-child{margin-top:0;}
.calc-box{background:var(--primary-light); border-radius:12px; padding:14px;}
.print-area{background:#fff; color:#111; padding:26px;}
.letterhead{border-bottom:3px solid var(--primary); padding-bottom:12px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center;}
.sign-box{border-top:1px solid #999; margin-top:46px; padding-top:6px; font-size:.85rem; text-align:center;}
@media print{
  body *{visibility:hidden;}
  #printArea, #printArea *{visibility:visible;}
  #printArea{position:absolute; top:0; left:0; width:100%;}
}
.form-label{font-size:.83rem;font-weight:600;color:var(--text);}
.small-note{font-size:.75rem;color:var(--muted);}
</style>
