{{--
    Product Specification Card
    
    When used standalone in a row, wrap it yourself:
      <div class="col-md-3">@include('components.product-spec-card')</div>
    
    When embedded inside another card (like Configuration Preview),
    it will fill the parent width automatically.
--}}

<div id="productSpecCol">
    <div class="card shadow product-spec-inner-card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0">Product Spec</h4>
        </div>
        <div class="card-body p-0">
            <div id="specBody" style="max-height:80vh; overflow:auto; background:#f5f5f5;">

                {{-- Inline styles so they always apply --}}
                <style>
                    .cs-section { background:#fff; margin-bottom:2px; }
                    .cs-title {
                        background:#4a5568; color:#fff; font-size:11px; font-weight:700;
                        text-transform:uppercase; padding:6px 12px; letter-spacing:0.5px;
                    }
                    .cs-tbl { width:100%; border-collapse:collapse; font-size:11px; background:#fff; }
                    .cs-tbl td {
                        padding:5px 12px; vertical-align:top;
                        border-bottom:1px solid #edf2f7; line-height:1.4;
                    }
                    .cs-tbl tr:last-child td { border-bottom:none; }
                    .cs-tbl td:first-child {
                        color:#718096; font-weight:600; width:120px; white-space:nowrap;
                    }
                    .cs-tbl td:last-child { color:#1a202c; font-weight:700; }
                    .cs-content { padding:6px 12px; background:#fff; font-size:11px; }

                    .cs-field {
                        display:flex; padding:5px 12px; background:#fff;
                        border-bottom:1px solid #edf2f7; font-size:11px; gap:8px; align-items:center;
                    }
                    .cs-field:last-child { border-bottom:none; }
                    .cs-field-label { color:#718096; font-weight:600; min-width:55px; }
                    .cs-field-badge {
                        display:inline-block; padding:1px 8px; border-radius:3px;
                        font-size:10px; font-weight:800; text-align:center;
                    }
                    .cs-field-badge.fix  { background:#edf2f7; color:#718096; }
                    .cs-field-badge.xfix { background:#dbeafe; color:#1e40af; }
                    .cs-field-badge.vent { background:#d1fae5; color:#065f46; }
                    .cs-field-desc { color:#4a5568; }

                    .cs-profile-line {
                        padding:4px 12px; font-size:11px; line-height:1.5;
                        border-bottom:1px solid #edf2f7; background:#fff;
                    }
                    .cs-profile-line:last-child { border-bottom:none; }
                    .cs-profile-line strong { color:#1a202c; margin-right:8px; }
                    .cs-profile-line .plbl { color:#718096; }

                    .cs-glass-sub {
                        padding:3px 12px 3px 24px; font-size:10.5px;
                        color:#718096; border-bottom:1px solid #f7fafc; background:#fff;
                    }
                    .cs-vo-title {
                        padding:5px 12px; font-weight:700; font-size:11px;
                        color:#1a202c; border-bottom:1px solid #edf2f7; background:#fff;
                    }
                    .cs-vo-sub {
                        padding:2px 12px 2px 24px; font-size:10.5px;
                        color:#4a5568; border-bottom:1px solid #f7fafc; background:#fff;
                    }

                    .cs-bom-hdr {
                        display:flex; align-items:center; gap:6px;
                        padding:7px 12px; background:#edf2f7; border-bottom:1px solid #e2e8f0;
                        cursor:pointer; font-size:11px; font-weight:700; color:#2d3748;
                    }
                    .cs-bom-hdr:hover { background:#e2e8f0; }
                    .cs-bom-hdr .cs-arrow { font-size:9px; transition:transform .15s; color:#718096; }
                    .cs-bom-hdr.open .cs-arrow { transform:rotate(90deg); }
                    .cs-bom-cnt { margin-left:auto; color:#a0aec0; font-weight:400; font-size:10px; }
                    .cs-bom-body { display:none; background:#fff; }
                    .cs-bom-body.show { display:block; }
                    .cs-bom-item {
                        display:flex; padding:4px 12px 4px 20px;
                        border-bottom:1px solid #f7fafc; font-size:10.5px; gap:8px;
                    }
                    .cs-bom-item:last-child { border-bottom:none; }
                    .cs-bom-code { font-weight:700; color:#2d3748; min-width:80px; flex-shrink:0; }
                    .cs-bom-desc { color:#4a5568; }
                    .cs-bom-sub {
                        padding:1px 12px 1px 104px; font-size:10px;
                        color:#a0aec0; border-bottom:1px solid #fafafa; background:#fff;
                    }

                    /* ── Cut List table ── */
                    .cs-cut-tbl { width:100%; border-collapse:collapse; font-size:10.5px; background:#fff; }
                    .cs-cut-tbl th {
                        background:#edf2f7; padding:5px 8px; text-align:left;
                        font-weight:700; color:#4a5568; font-size:10px;
                        text-transform:uppercase; letter-spacing:0.3px;
                        border-bottom:2px solid #e2e8f0; white-space:nowrap;
                    }
                    .cs-cut-tbl td {
                        padding:4px 8px; border-bottom:1px solid #f7fafc;
                        vertical-align:middle; color:#2d3748;
                    }
                    .cs-cut-tbl tr:hover td { background:#f7fafc; }
                    .cs-cut-type-badge {
                        display:inline-block; padding:1px 6px; border-radius:3px;
                        font-size:9px; font-weight:800; text-transform:uppercase;
                    }
                    .cs-cut-type-badge.frame   { background:#dbeafe; color:#1e40af; }
                    .cs-cut-type-badge.sash    { background:#d1fae5; color:#065f46; }
                    .cs-cut-type-badge.mullion { background:#fef3c7; color:#92400e; }
                    .cs-cut-type-badge.glass   { background:#ede9fe; color:#5b21b6; }
                    .cs-cut-type-badge.screen  { background:#fce7f3; color:#9d174d; }
                    .cs-cut-type-badge.other   { background:#edf2f7; color:#718096; }

                    /* ── When embedded inside #product-spec-section, strip card chrome ── */
                    #product-spec-section #productSpecCol {
                        width: 100% !important;
                        max-width: 100% !important;
                        padding: 0 !important;
                        margin: 0 !important;
                    }
                    #product-spec-section .product-spec-inner-card {
                        box-shadow: none !important;
                        border: none !important;
                        border-radius: 0 !important;
                    }
                    #product-spec-section .product-spec-inner-card > .card-header {
                        padding: 6px 12px !important;
                    }
                    #product-spec-section .product-spec-inner-card > .card-header h4 {
                        font-size: 13px !important;
                    }
                    #product-spec-section .product-spec-inner-card > .card-body {
                        padding: 0 !important;
                    }
                    #product-spec-section #specBody {
                        max-height: none !important;
                        overflow: visible !important;
                    }
                </style>

                {{-- UNIT --}}
                <div class="cs-section">
                    <div class="cs-title">Unit</div>
                    <table class="cs-tbl">
                        <tr><td>{{ __('Product Code') }}</td><td id="csProduct">--</td></tr>
                        <tr><td>{{ __('Product Type') }}</td><td id="csProductType">--</td></tr>
                        <tr><td>{{ __('Frame') }}</td><td id="csFrame">--</td></tr>
                        <tr><td>{{ __('Total Dimension') }}</td><td id="csSize">--</td></tr>
                        <tr><td>{{ __('Color') }}</td><td id="csColor">--</td></tr>
                        <tr><td>{{ __('Color (exterior)') }}</td><td id="csColorExt">--</td></tr>
                        <tr><td>{{ __('Color (interior)') }}</td><td id="csColorInt">--</td></tr>
                    </table>
                </div>

                {{-- PROFILES --}}
                <div class="cs-section">
                    <div class="cs-title">Profiles</div>
                    <div id="csProfiles"><div class="cs-content text-muted">--</div></div>
                </div>

                {{-- HARDWARE --}}
                <div class="cs-section">
                    <div class="cs-title">Hardware</div>
                    <table class="cs-tbl"><tr><td>{{ __('Hardware Type') }}</td><td id="csHwType">--</td></tr></table>
                    <div id="csFields"></div>
                </div>

                {{-- GLASS --}}
                <div class="cs-section">
                    <div class="cs-title">Glass / Insert</div>
                    <table class="cs-tbl"><tr><td>{{ __('GLASS') }}</td><td id="csGlassName">--</td></tr></table>
                    <div id="csGlassDetail"></div>
                </div>

                {{-- ACCESSORIES --}}
                <div class="cs-section">
                    <div class="cs-title">Accessories</div>
                    <table class="cs-tbl"><tr><td>{{ __('GLOBAL') }}</td><td>{{ __('GLOBAL ITEM FOR ORDER') }}</td></tr></table>
                </div>

                {{-- VALUE OPTION --}}
                <div class="cs-section">
                    <div class="cs-title">Value Option</div>
                    <div id="csValueOption"></div>
                </div>

                {{-- PREXHS --}}
                <div class="cs-section">
                    <div class="cs-title">PREXHS – Profile Exchange</div>
                    <div id="csPrexhsSvg" style="padding:8px 12px; background:#fff; max-width:240px; margin:0 auto;"></div>
                    <div id="csPrexhs"><div class="cs-content text-muted">--</div></div>
                </div>

                {{-- FIELD BOM --}}
                <div class="cs-section">
                    <div class="cs-title">Field BOM</div>
                    <div id="csFbom"></div>
                </div>

                {{-- NFRC --}}
                <div class="cs-section">
                    <div class="cs-title">NFRC</div>
                    <table class="cs-tbl">
                        <tr><td>U-FACTOR</td><td id="csUfactor">--</td></tr>
                        <tr><td>{{ __('SHGC') }}</td><td id="csShgc">--</td></tr>
                        <tr><td>{{ __('VT') }}</td><td id="csVt">--</td></tr>
                        <tr><td>{{ __('CR') }}</td><td id="csCr">--</td></tr>
                    </table>
                </div>

                {{-- MEASUREMENTS --}}
                <div class="cs-section">
                    <div class="cs-title">Measurements</div>
                    <table class="cs-tbl">
                        <tr><td>{{ __('Rough Opening') }}</td><td id="csRough">--</td></tr>
                        <tbody id="csGlassFields"></tbody>
                        <tr><td>{{ __('Screen') }}</td><td id="csScreen">--</td></tr>
                        <tr><td>{{ __('Area') }}</td><td id="csArea">--</td></tr>
                    </table>
                </div>

                {{-- CUT LIST --}}
                <div class="cs-section">
                    <div class="cs-title">Cut List</div>
                    <div id="csCutList"><div class="cs-content text-muted">--</div></div>
                </div>

                {{-- DEDUCTIONS (from Profile Management) --}}
                <div class="cs-section">
                    <div class="cs-title">Deductions Applied</div>
                    <div id="csDeductions"><div class="cs-content text-muted">--</div></div>
                </div>

            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    // Live NFRC map keyed by SeriesConfiguration.series_type,
    // loaded from /rating/nfrc-master/series-map.json
    var SERIES_NFRC_MAP = {};
    fetch('{{ Route::has("admin.rating.nfrc_master.seriesMap") ? route("admin.rating.nfrc_master.seriesMap") : "/rating/nfrc-master/series-map.json" }}', { credentials: 'same-origin' })
        .then(function(r){ return r.ok ? r.json() : null; })
        .then(function(j){ if (j && j.map) { SERIES_NFRC_MAP = j.map; if (typeof load === 'function') load(); } })
        .catch(function(){});

    var S={
        series:document.getElementById('seriesSelect'),
        type:document.getElementById('seriesTypeSelect'),
        w:document.querySelector('input[name="width"]'),
        wDec:document.getElementById('width_decimal'),
        h:document.querySelector('input[name="height"]'),
        hDec:document.getElementById('height_decimal'),
        glass:document.querySelector('[name="glass_type"]'),
        frame:document.querySelector('[name="frame_type"]'),
        cE:document.getElementById('colorExteriorSelect'),
        cI:document.getElementById('colorInteriorSelect'),
    };
    var tmr=null;
    function kick(){clearTimeout(tmr);tmr=setTimeout(load,400);}
    for(var k in S){if(S[k]){S[k].addEventListener('change',kick);if(S[k].tagName==='INPUT')S[k].addEventListener('input',kick);}}

    function $(id){return document.getElementById(id);}
    function t(id,v){var e=$(id);if(e)e.textContent=v||'--';}
    function ht(id,v){var e=$(id);if(e)e.innerHTML=v;}

    // Read width/height from the hidden decimal fields first (handles fractions like "67 1/2" → 67.5)
    // Falls back to parseFraction on the visible input, then raw parseFloat
    function getW(){
        var dec=S.wDec?parseFloat(S.wDec.value):0;
        if(dec>0) return dec;
        if(typeof parseFraction==='function'&&S.w&&S.w.value) return parseFraction(S.w.value)||0;
        return parseFloat(S.w?S.w.value:0)||0;
    }
    function getH(){
        var dec=S.hDec?parseFloat(S.hDec.value):0;
        if(dec>0) return dec;
        if(typeof parseFraction==='function'&&S.h&&S.h.value) return parseFraction(S.h.value)||0;
        return parseFloat(S.h?S.h.value:0)||0;
    }

    function load(){
        var sId=S.series?S.series.value:'',sT=S.type?S.type.value:'';
        var W=getW(),H=getH();
        if(!sId||!sT||W<=0||H<=0)return;

        var cExt=S.cE?S.cE.value:'WH',cInt=S.cI?S.cI.value:'WH';
        var glass=S.glass?S.glass.value:'LE3/CLR',frame=S.frame?S.frame.value:'Retrofit';

        // Detect whether each side is a laminate-group pick
        var extOpt=S.cE?S.cE.selectedOptions[0]:null;
        var intOpt=S.cI?S.cI.selectedOptions[0]:null;
        var extIsLam=extOpt&&extOpt.dataset&&extOpt.dataset.group==='laminate';
        var intIsLam=intOpt&&intOpt.dataset&&intOpt.dataset.group==='laminate';
        var extName=(extOpt?extOpt.textContent:cExt).trim().toUpperCase();
        var intName=(intOpt?intOpt.textContent:cInt).trim().toUpperCase();

        var cfgExt=extIsLam?('LAM ('+cExt+')'):cExt;
        var cfgInt=intIsLam?('LAM ('+cInt+')'):cInt;
        var extLabel=extIsLam?('LAM - '+cExt):(cExt+'  '+extName);
        var intLabel=intIsLam?('LAM - '+cInt):(cInt+'  '+intName);

        t('csProduct',sT+'  Combo Unit');
        t('csProductType','2101  VINYL DYNAMIC SLIDING WINDOW');
        t('csFrame',frame.toUpperCase());
        t('csSize',W+' x '+H);
        t('csColor',cfgExt+' - '+cfgInt);
        t('csColorExt',extLabel);
        t('csColorInt',intLabel);

        var fullType=sT.toUpperCase().replace(/^(DYNAMIC-|PRESTIGE-|IM-|GS-|GX-|GSCO-)/,'').replace(/-(?!B[XO\d]|T[XO\d]).*$/,'');
        var baseType=fullType.replace(/-(B[XO]+|T[XO]+|B\d|T\d|BA|TA|T\dB\d|T\dB[XO]+).*$/,'').replace(/-.*$/,'');
        var fields=parseFields(fullType);
        renderFields(fields,W,H,baseType,fullType);

        var nmap={'CLR/CLR':{u:'0.47',s:'0.56',v:'0.63',c:'45'},'LE3/CLR':{u:'0.28',s:'0.22',v:'0.52',c:'62'},'LE3/LAM':{u:'0.28',s:'0.20',v:'0.41',c:'62'}};
        // Prefer the NFRC row mapped to the selected series_type; fall back to glass-type map
        var n = (sT && SERIES_NFRC_MAP[sT]) ? SERIES_NFRC_MAP[sT] : (nmap[glass] || nmap['LE3/CLR']);
        t('csUfactor',n.u);t('csShgc',n.s);t('csVt',n.v);t('csCr',n.c);

        t('csGlassName','Double Glazed IG');
        ht('csGlassDetail',
            '<div class="cs-glass-sub">Data -Q</div>'+
            '<div class="cs-glass-sub"><strong>1800</strong> &nbsp; GLASS</div>'+
            '<div class="cs-glass-sub">BREATHER TUBE: 0</div>'+
            '<div class="cs-glass-sub">IGH THICKNESS FOR COMPLETE</div>'+
            '<div class="cs-glass-sub">WINDOWS/DOORS: 0.7500</div>'
        );

        // Temporary defaults — will be overwritten by API response from profile management
        t('csRough',W+'" × '+H+'"');
        t('csGlassFix','calculating...');
        t('csGlassSash','calculating...');
        t('csScreen','calculating...');
        t('csArea',((W*H)/144).toFixed(1)+' sqft');

        ht('csValueOption',
            '<div class="cs-vo-title">1888 &nbsp; VALUE OPTION</div>'+
            vo('SERIES: IM')+vo('DOOR OR WINDOW: 1')+
            vo('COUNT FIX FIELD: '+fields.filter(function(f){return f.t==='FIX';}).length)+
            vo('COUNT XFIX FIELD: '+fields.filter(function(f){return f.t==='XFIX';}).length)+
            vo('COUNT VENT FIELD: '+fields.filter(function(f){return f.t==='VENT';}).length)+
            vo('COUNT SCREEN AMOUNT: 1')+
            vo('Manual Width: <span id="csManualW">'+W.toFixed(4)+'</span>')+
            vo('Manual Height: <span id="csManualH">'+H.toFixed(4)+'</span>')
        );

        // Try API
        fetch('/admin/quotes/configure',{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':(document.querySelector('meta[name="csrf-token"]')||{}).content||''},
            body:JSON.stringify({series_id:sId,series_type:sT,width:W,height:H,color_exterior:cExt,color_interior:cInt,glass_type:glass,frame_type:frame})
        }).then(function(r){return r.json();}).then(function(d){if(d.success)renderApi(d);}).catch(function(){});
    }

    function renderApi(d){
        // ── Measurements from Profile Management ──
        if(d.measurements){
            var m=d.measurements;
            if(m.rough_opening) t('csRough',m.rough_opening.width+'" × '+m.rough_opening.height+'"');

            // Per-field glass sizes
            var gfEl=document.getElementById('csGlassFields');
            if(gfEl && m.glass_fields && m.glass_fields.length>0){
                var html='';
                m.glass_fields.forEach(function(f){
                    var label='Field '+f.field+': '+f.label;
                    var typeTag=f.type==='sash'?' <span style="color:#e67e22;font-size:10px">(sash)</span>':' <span style="color:#2980b9;font-size:10px">(fix)</span>';
                    html+='<tr class="cs-field-measure-row" data-field-idx="'+f.field+'" style="cursor:pointer;transition:background .15s"><td>'+label+typeTag+'</td><td><strong>'+f.width+'" × '+f.height+'"</strong></td></tr>';
                });
                gfEl.innerHTML=html;
                // Click to highlight field in PREXHS sketch
                gfEl.querySelectorAll('.cs-field-measure-row').forEach(function(row){
                    row.addEventListener('click',function(){
                        var idx=parseInt(this.getAttribute('data-field-idx'));
                        highlightPrexhsField(idx);
                        // Toggle active row style
                        gfEl.querySelectorAll('.cs-field-measure-row').forEach(function(r){r.style.background='';});
                        this.style.background='#dbeafe';
                    });
                });
            } else if(m.glass_fix){
                // Fallback to old format
                var html='<tr><td>{{ __('Glass Fix') }}</td><td>'+m.glass_fix.w+'" × '+m.glass_fix.h+'"</td></tr>';
                if(m.glass_sash) html+='<tr><td>{{ __('Glass Sash') }}</td><td>'+m.glass_sash.w+'" × '+m.glass_sash.h+'"</td></tr>';
                if(gfEl) gfEl.innerHTML=html;
            }

            if(m.screen) t('csScreen',m.screen.w+'" × '+m.screen.h+'"');
            if(m.area_sqft) t('csArea',m.area_sqft+' sqft');

            // Manual Width / Height from API
            var mwEl=$('csManualW'), mhEl=$('csManualH');
            if(mwEl && m.manual_width) mwEl.textContent=parseFloat(m.manual_width).toFixed(4);
            if(mhEl && m.manual_height) mhEl.textContent=parseFloat(m.manual_height).toFixed(4);

            // Flag if no profile set was found
            if(m.from_profile_management===false){
                if(gfEl) gfEl.innerHTML='<tr><td>{{ __('Glass') }}</td><td>(no profile set linked)</td></tr>';
                t('csScreen','(no profile set linked)');
                ht('csDeductions','<div class="cs-content text-muted">No profile set found for this series type. Link one in Profile Management.</div>');
            }

            // ── Render deduction breakdown ──
            if(m.deductions&&m.deductions.length){
                var dhtml='';
                m.deductions.forEach(function(ded){
                    if(ded.source==='profile_set'){
                        dhtml+='<div class="cs-profile-line" style="background:#eff6ff;padding:4px 6px;border-radius:4px;"><span class="plbl">Profile Set: </span><strong style="color:#1e40af;">'+ded.profile_set+'</strong></div>';
                        var fwInfo = ded.fix_field_width ? 'Fix: <strong>'+ded.fix_field_width+'"</strong>' : '';
                        if(ded.sash_field_width && ded.sash_field_width !== ded.fix_field_width) fwInfo += ' &nbsp; Sash: <strong>'+ded.sash_field_width+'"</strong>';
                        dhtml+='<div class="cs-profile-line"><span class="plbl">Type: </span><strong>'+(ded.series_type||ded.window_type||'')+'</strong> ('+ded.panel_count+' panel'+(ded.panel_count>1?'s':'')+') &nbsp; '+fwInfo+(ded.has_sash?'':' &nbsp; <em style="color:#64748b;">No sash</em>')+'</div>';
                        dhtml+='<div class="cs-profile-line"><span class="plbl">Source: </span><strong>'+ded.data_source+'</strong></div>';
                        if(ded.manipulation_count>0){
                            var ghd=ded.glass_h_delta||0, gvd=ded.glass_v_delta||0;
                            dhtml+='<div class="cs-profile-line" style="background:#dcfce7;padding:4px 6px;border-radius:4px;margin-top:4px;"><span class="plbl">Manipulations: </span><strong style="color:#166534;">'+ded.manipulation_count+' loaded</strong>';
                            if(ghd||gvd) dhtml+=' &nbsp; Glass Δ: H=<strong>'+ghd+'"</strong> V=<strong>'+gvd+'"</strong>';
                            var shDelta=ded.sash_h_delta||0, svDelta=ded.sash_v_delta||0;
                            if(shDelta||svDelta) dhtml+=' &nbsp; Sash Δ: H=<strong>'+shDelta+'"</strong> V=<strong>'+svDelta+'"</strong>';
                            dhtml+='</div>';
                            if(ded.manipulation_context){
                                var mc=ded.manipulation_context;
                                dhtml+='<div class="cs-profile-line" style="font-size:10px;color:#64748b;">Context: frame=<b>'+mc.frame_type+'</b> article=<b>'+mc.article+'</b> product=<b>'+(mc.product_type_code||'null')+'</b></div>';
                            }
                            if(ded.manipulation_debug&&ded.manipulation_debug.length){
                                ded.manipulation_debug.forEach(function(md){
                                    var color=md.matched?'#166534':'#dc2626';
                                    var icon=md.matched?'✓':'✗';
                                    var pos=md.position?(' ['+md.position+']'):'';
                                    var multInfo = (md.h_mult !== undefined ? ' H×'+md.h_mult+' V×'+md.v_mult : '');
                                    dhtml+='<div class="cs-profile-line" style="font-size:10px;color:'+color+';">'+icon+' #'+md.seq+' '+(md.component||'')+pos+multInfo+' ft=<b>'+(md.frame_type||'*')+'</b> art=<b>'+(md.article||'*')+'</b> pt=<b>'+(md.product_type||'*')+'</b> d1='+md.diff1+' d3='+md.diff3+'</div>';
                                });
                            }
                        }else if(typeof ded.manipulation_count !== 'undefined'){
                            dhtml+='<div class="cs-profile-line" style="font-size:10px;color:#94a3b8;">No deduction manipulations loaded</div>';
                        }
                        var fhd=ded.fix_h_ded||ded.set_frame_h_ded||0, fvd=ded.fix_v_ded||ded.set_frame_v_ded||0;
                        var shd=ded.sash_h_ded||ded.set_sash_h_ded||0, svd=ded.sash_v_ded||ded.set_sash_v_ded||0;
                        if(fhd>0||fvd>0){
                            dhtml+='<div class="cs-profile-line"><span class="plbl">Fix Glass Ded: </span>H='+fhd+'" V='+fvd+'"</div>';
                            dhtml+='<div class="cs-profile-line"><span class="plbl">Sash Glass Ded: </span>H='+shd+'" V='+svd+'"</div>';
                            if(ded.screen_h_ded>0||ded.screen_v_ded>0) dhtml+='<div class="cs-profile-line"><span class="plbl">Screen Ded: </span>H='+ded.screen_h_ded+'" V='+ded.screen_v_ded+'"</div>';
                            if(ded.set_interlock_ded>0||ded.set_meeting_rail_ded>0) dhtml+='<div class="cs-profile-line"><span class="plbl">Interlock: </span>'+ded.set_interlock_ded+'" &nbsp; <span class="plbl">Meeting Rail: </span>'+ded.set_meeting_rail_ded+'"</div>';
                        }
                        if(ded.catalog_a1>0) dhtml+='<div class="cs-profile-line"><span class="plbl">Catalog A1: </span>'+ded.catalog_a1+'" &nbsp; <span class="plbl">A2: </span>'+ded.catalog_a2+'"</div>';
                    }else if(ded.source==='deduction_manager'){
                        dhtml+='<div class="cs-profile-line" style="background:#fef3c7;padding:4px 6px;border-radius:4px;"><span class="plbl">Source: </span><strong style="color:#92400e;">Deduction Manager (fallback)</strong></div>';
                        if(ded.rules&&ded.rules.length){
                            ded.rules.forEach(function(r){
                                var label=(r.description||r.component)+' ('+r.dimension+')';
                                dhtml+='<div class="cs-profile-line"><span class="plbl">'+label+': </span><code style="font-size:10px;background:#edf2f7;padding:1px 4px;border-radius:2px;">'+r.formula+'</code> = <strong>'+r.result+'"</strong></div>';
                            });
                        }
                    }else if(ded.source==='components'&&ded.items){
                        dhtml+='<div class="cs-profile-line" style="margin-top:4px;"><strong>Components ('+ded.items.length+'):</strong></div>';
                        ded.items.forEach(function(c){
                            var badge='<span class="cs-field-badge fix" style="font-size:9px;margin-right:4px;">'+c.type+'</span>';
                            dhtml+='<div class="cs-profile-line">'+badge+'<strong>'+c.profile_code+'</strong> <span class="plbl">('+c.dimension+')</span> '+c.formula+' = <strong>'+c.result+'"</strong> ×'+c.qty+'</div>';
                        });
                    }
                });
                if(dhtml) ht('csDeductions',dhtml);
            }

            // ── Cut List ──
            if(m.cut_list && m.cut_list.length > 0){
                renderCutList(m.cut_list);
            }
        }

        if(d.profiles&&Object.keys(d.profiles).length){
            var html='',prex='';
            for(var k in d.profiles){
                var v=d.profiles[k];
                var val=typeof v==='string'?v:(v.ident||'--');
                var lbl=typeof v==='string'?k:(v.type||k);
                html+=pl(val,lbl);prex+=pl(lbl.toUpperCase(),val);
            }
            ht('csProfiles',html);ht('csPrexhs',prex);
        }
        if(d.hardware_bom&&d.hardware_bom.field_bom){
            var html='';
            d.hardware_bom.field_bom.forEach(function(f){
                var uid='fbom'+f.field_position;
                html+=bHdr(uid,'Field '+f.field_position+': '+f.hardware_type,(f.items||[]).length);
                html+='<div class="cs-bom-body" id="'+uid+'">';
                (f.items||[]).forEach(function(it){
                    html+=bItem(it.part||'',it.description||'');
                    if(it.quantity)html+=bSub('QUANTITY: '+it.quantity);
                    if(it.length)html+=bSub('LENGTH: '+it.length.toFixed(4));
                    if(it.color)html+=bSub('COLOR: '+it.color);
                    if(it.position)html+=bSub('POSITION: '+it.position);
                });
                html+='</div>';
            });
            ht('csFbom',html);
        }
    }

    function renderFields(fields,W,H,base,fullType){
        var desc={FIX:'Fixed for Single Slider and Single Hung',XFIX:'Fixed for Single Slider and Single Hung',XR:'Single Slider Slide Right Sash',XL:'Single Slider Slide Left Sash',VT:'Single/Double Hung Operable Sash',CL:'Casement Left Sash',CR:'Casement Right Sash',AW:'Awning Sash'};
        t('csHwType','2000  Value Windows Vinyl Series');
        var html='';
        fields.forEach(function(f){
            var cls=f.t==='FIX'?'fix':f.t==='XFIX'?'xfix':'vent';
            html+='<div class="cs-field"><span class="cs-field-label">Field '+f.p+'</span><span class="cs-field-badge '+cls+'">'+f.h+'</span><span class="cs-field-desc">'+(desc[f.h]||'')+'</span></div>';
        });
        ht('csFields',html);

        ht('csProfiles',
            pl('FEI-8001','Imperial Slider and Single Hung Frame, Retro-Fit')+
            pl('FEI-8009','Imperial and GX Slider Sash Jamb')+
            pl('FEI-8004','Imperial Slider & Hung Mullion')+
            pl('FEI-8007','Imperial and GX Slider Sash Interlock')+
            pl('FEI-8006','Imperial Slider & Hung Meeting Rail')
        );

        var prexMap = {
            'FRAME TOP':'FEI-8001','FRAME BOTTOM':'FEI-8001',
            'FRAME LEFT':'FEI-8001','FRAME RIGHT':'FEI-8001',
            'HS SASH':'FEI-8009','SASH HINGE SIDE':'FEI-8007',
            'MULLION MEETING RAIL':'FEI-8006','MULLION SPECIAL':'HSASTR',
            'HEAVY DUTY MULLION':'FEI-8004'
        };
        var prexHtml = '';
        for (var pk in prexMap) {
            prexHtml += '<div class="cs-profile-line cs-prex-row" data-prex-key="'+pk+'" style="cursor:pointer;padding:3px 6px;border-radius:4px;transition:background .15s,color .15s"><strong>'+pk+'</strong> <span class="plbl">'+prexMap[pk]+'</span></div>';
        }
        ht('csPrexhs', prexHtml);
        // Pass shape info so Profile Exchange SVG matches shape outline
        var _shapeCode = (document.getElementById('shape_code') ? document.getElementById('shape_code').value : '') || '';
        var _shapeParams = (document.getElementById('shape_params') ? document.getElementById('shape_params').value : '') || '';
        renderPrexhsSvg(base, prexMap, fullType, W, H, _shapeCode, _shapeParams);

        var fbom='';
        fields.forEach(function(f){
            var uid='fbom'+f.p;
            fbom+=bHdr(uid,'Field '+f.p+': '+f.h);
            fbom+='<div class="cs-bom-body" id="'+uid+'">';
            if(f.t==='FIX'){
                fbom+=bItem('ANTILIFT','SLIDING WINDOW ANTILIFT')+bSub('QUANTITY: 1');
                fbom+=bItem('FEI-8011','IM,GX ANTI-LIFT')+bSub('COLOR: WHITE')+bSub('QUANTITY: 1');
                fbom+=bItem('SETBK','SETTING BLOCK')+bSub('QUANTITY: 6');
                fbom+=bItem('SCREW#6','#6 SCREW')+bSub('QUANTITY: 4');
                fbom+=bItem('SCRGLUE','SCREW GLUE')+bSub('QUANTITY: 1');
                ['O','U','L','R'].forEach(function(pos){fbom+=bItem('FEI-8008','SH WINDOW COVERS FOR IM')+bSub('POSITION: '+pos)+bSub('QUANTITY: 1');});
                fbom+=bItem('GLTAPE','DOUBLE-SIDE ADHESIVE TAPE FOR GLAZING');
            }else if(f.h==='XR'){
                fbom+=bItem('SETBK','SETTING BLOCK')+bSub('QUANTITY: 6');
                fbom+=bItem('STP','HS WINDOW STOPPER')+bSub('LENGTH: 3.0000')+bSub('POSITION: O');
                fbom+=bItem('GLTAPE','DOUBLE-SIDE ADHESIVE TAPE FOR GLAZING');
                fbom+=bItem('FEI-2008TI','HS IM AND GX TRACK')+bSub('POSITION: U');
            }else if(f.t==='XFIX'){
                fbom+=bItem('WLOCK','WINDOW LOCK FOR HS,SH')+bSub('QUANTITY: 1');
                fbom+=bItem('WSTRIKE','WINDOW LOCK STRIKE FOR HS,SH')+bSub('QUANTITY: 1');
                fbom+=bItem('WMECH','WINDOW LOCK MECHANISM')+bSub('QUANTITY: 1');
                fbom+=bItem('WLKCVRV','WINDOW LOCK COVER VALUE')+bSub('QUANTITY: 1');
                fbom+=bItem('SETBK','SETTING BLOCK')+bSub('QUANTITY: 6');
                fbom+=bItem('VTLAT','VENT-LATCH')+bSub('QUANTITY: 2');
                fbom+=bItem('SARLLR','STANDARD ALUMINUM ROLLER')+bSub('QUANTITY: 2');
                fbom+=bItem('WSCREEN','VINYL WINDOW SCREEN');
                fbom+=bItem('VHSSCREEN','VINYL WINDOW HORIZONTAL SLIDER SCREEN');
                fbom+=bItem('WSCR-CONA','SCREEN CONNECTOR A')+bSub('QUANTITY: 2');
                fbom+=bItem('WSCR-CONB','SCREEN CONNECTOR B')+bSub('QUANTITY: 2');
                fbom+=bItem('TSPR','SCREEN TENSION SPRING')+bSub('QUANTITY: 2');
                fbom+=bItem('AL-56-53','REGULAR SCREEN FRAME')+bSub('POSITION: H');
                fbom+=bItem('AL-56-53','REGULAR SCREEN FRAME')+bSub('POSITION: V');
                fbom+=bItem('SHLBL','SASH LABEL');
                fbom+=bItem('GLTAPE','DOUBLE-SIDE ADHESIVE TAPE FOR GLAZING');
            }
            fbom+='</div>';
        });
        fbom+=bHdr('fbomU','Unit Items');
        fbom+='<div class="cs-bom-body" id="fbomU">';
        fbom+=bItem('CBCNRS1','CARDBOARD CORNERS 3 3/16"')+bSub('QUANTITY: 4');
        fbom+=bItem('CBCNRS2','CARDBOARD CORNERS 4')+bSub('QUANTITY: 4');
        fbom+=bItem('FRLBL','FRAME LABEL')+bItem('AAMALBL','AAMA LABEL');
        fbom+=bItem('NFRCLBL','NFRC LABEL')+bItem('LOGOLBL','LOGO LABEL');
        fbom+=bItem('WRAPPING','WRAPPING');
        fbom+='</div>';
        ht('csFbom',fbom);
    }

    function pl(b,d){return '<div class="cs-profile-line"><strong>'+b+'</strong> <span class="plbl">'+d+'</span></div>';}
    function vo(t){return '<div class="cs-vo-sub">'+t+'</div>';}
    function bHdr(uid,label,cnt){
        return '<div class="cs-bom-hdr" onclick="this.classList.toggle(\'open\');document.getElementById(\''+uid+'\').classList.toggle(\'show\');"><span class="cs-arrow">&#9654;</span> '+label+(cnt!==undefined?'<span class="cs-bom-cnt">('+cnt+')</span>':'')+'</div>';
    }
    function bItem(c,d){return '<div class="cs-bom-item"><span class="cs-bom-code">'+c+'</span><span class="cs-bom-desc">'+d+'</span></div>';}
    function bSub(t){return '<div class="cs-bom-sub">'+t+'</div>';}

    // ═══ Cut List Renderer ═══
    function renderCutList(items){
        if(!items||!items.length){ ht('csCutList','<div class="cs-content text-muted">No components</div>'); return; }

        // Group by category: Frame, Sash, Mullion, Glass, Screen, Other
        var groups = {};
        var order = ['Frame','Sash','Mullion','Glass','Screen'];
        var typeMap = {
            'frame':'Frame','retrofit_frame':'Frame','jamb':'Frame',
            'sash':'Sash','sash_interlock':'Sash','sash_rail':'Sash',
            'mullion':'Mullion','meeting_rail':'Mullion',
            'glass_fix':'Glass','glass_sash':'Glass',
            'screen':'Screen','grid':'Screen'
        };
        items.forEach(function(c){
            var raw = (c.type || 'other').toLowerCase();
            var gn = typeMap[raw] || 'Other';
            if(order.indexOf(gn)<0) order.push(gn);
            if(!groups[gn]) groups[gn]=[];
            groups[gn].push(c);
        });

        var html='';
        order.forEach(function(grp){
            if(!groups[grp]) return;
            var cls = grp.toLowerCase();
            if(cls!=='frame'&&cls!=='sash'&&cls!=='mullion'&&cls!=='glass'&&cls!=='screen') cls='other';
            var uid='cutgrp_'+grp.replace(/\s/g,'_');

            // Collapsible header
            html+='<div class="cs-bom-hdr" onclick="this.classList.toggle(\'open\');document.getElementById(\''+uid+'\').classList.toggle(\'show\');">';
            html+='<span class="cs-arrow">&#9654;</span> ';
            html+='<span class="cs-cut-type-badge '+cls+'">'+grp+'</span>';
            html+='<span style="margin-left:4px;">Cuts</span>';
            html+='<span class="cs-bom-cnt">('+groups[grp].length+')</span>';
            html+='</div>';

            // Table body
            html+='<div class="cs-bom-body" id="'+uid+'">';
            html+='<table class="cs-cut-tbl">';
            html+='<thead><tr><th>{{ __('Profile') }}</th><th>{{ __('Description') }}</th><th>{{ __('Cut Length') }}</th><th>{{ __('Qty') }}</th><th>{{ __('Orient.') }}</th><th>{{ __('Cut') }}</th></tr></thead>';
            html+='<tbody>';
            groups[grp].forEach(function(c){
                var orient = c.orientation ? c.orientation.charAt(0).toUpperCase() : '—';
                var cutType = c.cut_type ? c.cut_type.charAt(0).toUpperCase()+c.cut_type.slice(1) : '—';
                var desc = c.description || c.dimension || '—';
                var formula = c.formula ? ' <span style="color:#a0aec0;font-size:9px;">(' + c.formula + ')</span>' : '';
                html+='<tr>';
                html+='<td><strong>'+c.profile_code+'</strong>'+formula+'</td>';
                html+='<td>'+desc+'</td>';
                html+='<td style="font-weight:700;font-family:monospace;">'+c.result.toFixed(4)+'"</td>';
                html+='<td style="text-align:center;">×'+c.qty+'</td>';
                html+='<td>'+orient+'</td>';
                html+='<td>'+cutType+'</td>';
                html+='</tr>';
            });
            html+='</tbody></table></div>';
        });

        // Summary row
        var totalPcs = 0;
        items.forEach(function(c){ totalPcs += c.qty; });
        html+='<div style="padding:5px 8px;font-size:10px;color:#718096;background:#f7fafc;border-top:1px solid #e2e8f0;">Total: <strong>'+items.length+'</strong> profile'+
            (items.length>1?'s':'')+' / <strong>'+totalPcs+'</strong> piece'+(totalPcs>1?'s':'')+'</div>';

        ht('csCutList', html);
    }

    // ═══ SVG Window Cross-Section for PREXHS ═══
    var _prexSelectedKey = null;

    // Map every PREXHS key to what it highlights in the SVG
    var _prexKeyMap = {
        'FRAME TOP':    {type:'frame', side:'top'},
        'FRAME BOTTOM': {type:'frame', side:'bottom'},
        'FRAME LEFT':   {type:'frame', side:'left'},
        'FRAME RIGHT':  {type:'frame', side:'right'},
        'HS SASH':              {type:'vent'},
        'SASH HINGE SIDE':      {type:'vent'},
        'MULLION MEETING RAIL': {type:'divider'},
        'MULLION SPECIAL':      {type:'divider'},
        'HEAVY DUTY MULLION':   {type:'divider'}
    };

    function _prexHighlight(key) {
        _prexSelectedKey = (_prexSelectedKey === key) ? null : key;
        var active = _prexKeyMap[_prexSelectedKey] || null;

        // ── Reset all frame sides ──
        ['top','bottom','left','right'].forEach(function(s) {
            var rect = document.getElementById('prex-frame-'+s);
            var txt  = document.getElementById('prex-label-'+s);
            if (!rect || !txt) return;
            var isActive = active && active.type === 'frame' && active.side === s;
            rect.setAttribute('fill',    isActive ? '#dc2626' : '#dbeafe');
            rect.setAttribute('opacity', isActive ? '0.85' : '0.5');
            txt.setAttribute('fill',     isActive ? '#ffffff' : '#1e40af');
        });

        // ── Reset sash panels ──
        var sashEls = document.querySelectorAll('.prex-sash-el');
        for (var v = 0; v < sashEls.length; v++) {
            var isVentActive = active && active.type === 'vent';
            sashEls[v].setAttribute('stroke',       isVentActive ? '#7c3aed' : '#64748b');
            sashEls[v].setAttribute('stroke-width',  isVentActive ? '3' : '1.8');
            sashEls[v].setAttribute('fill',          isVentActive ? '#c4b5fd' : '#d4e4f7');
        }

        // ── Reset mullion elements ──
        var mullEls = document.querySelectorAll('.prex-mullion-el');
        for (var d = 0; d < mullEls.length; d++) {
            var isDivActive = active && active.type === 'divider';
            mullEls[d].setAttribute('fill',         isDivActive ? '#fbbf24' : '#b0b8c4');
            mullEls[d].setAttribute('stroke',       isDivActive ? '#92400e' : '#64748b');
            mullEls[d].setAttribute('stroke-width',  isDivActive ? '2' : '0.8');
        }

        // ── Highlight matching text row ──
        var rows = document.querySelectorAll('.cs-prex-row[data-prex-key]');
        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            if (row.getAttribute('data-prex-key') === _prexSelectedKey) {
                row.style.background = '#dc2626';
                row.style.color = '#ffffff';
            } else {
                row.style.background = '';
                row.style.color = '';
            }
        }
    }

    // Auto-generate SVG layout for any XO-combination type not explicitly listed
    function autoLayout(code) {
        if (!code || !/^[XO]{2,}$/.test(code)) return null;
        var n = code.length;
        var w = 1 / n;
        var panels = [];
        var sashDirs = ['left','right'];
        var si = 0;
        for (var i = 0; i < n; i++) {
            var ch = code[i];
            if (ch === 'X') {
                panels.push({x: i*w, w: w, type:'vent', dir: sashDirs[si % 2] || 'right'});
                si++;
            } else {
                panels.push({x: i*w, w: w, type:'fix'});
            }
        }
        return { panels: panels, hasMullion: n > 1 };
    }

    // Auto-generate parseFields entry for compound/unknown types
    // Tokenizes: AW2VOAW2V → [AW×2, O×1, AW×2] → 5 fields
    function autoParseFields(code) {
        if (!code) return null;
        // Panel tokens to match, longest first
        var tokens = [
            'OXOXO','XOXOX','OXXOO','OOXOO',
            'OXXO','XOOX','XOXO','OXOX',
            'XXO','OXX','OOX','XOO','XOX','OXO',
            'AW','CL','CR','CM','SH','DH','PW','SW',
            'XO','OX',
            'SL','F','X','O'
        ];
        var sashTypes = {'X':1,'AW':1,'CL':1,'CR':1,'CM':1,'SH':1,'DH':1,'SL':1,'SW':1};
        // Known XO sub-layouts
        var xoLayouts = {
            'OXXO':[{h:'FIX',t:'FIX'},{h:'XL',t:'VENT'},{h:'XR',t:'VENT'},{h:'FIX',t:'FIX'}],
            'XOOX':[{h:'XL',t:'VENT'},{h:'FIX',t:'FIX'},{h:'FIX',t:'FIX'},{h:'XR',t:'VENT'}],
            'XOXO':[{h:'XL',t:'VENT'},{h:'FIX',t:'FIX'},{h:'XR',t:'VENT'},{h:'FIX',t:'FIX'}],
            'OXOX':[{h:'FIX',t:'FIX'},{h:'XL',t:'VENT'},{h:'FIX',t:'FIX'},{h:'XR',t:'VENT'}],
            'OXOXO':[{h:'FIX',t:'FIX'},{h:'XL',t:'VENT'},{h:'FIX',t:'FIX'},{h:'XR',t:'VENT'},{h:'FIX',t:'FIX'}],
            'XOXOX':[{h:'XL',t:'VENT'},{h:'FIX',t:'FIX'},{h:'XR',t:'VENT'},{h:'FIX',t:'FIX'},{h:'X3',t:'VENT'}],
            'XXO':[{h:'XL',t:'VENT'},{h:'XR',t:'VENT'},{h:'FIX',t:'FIX'}],
            'OXX':[{h:'FIX',t:'FIX'},{h:'XL',t:'VENT'},{h:'XR',t:'VENT'}],
            'OOX':[{h:'FIX',t:'FIX'},{h:'FIX',t:'FIX'},{h:'XR',t:'VENT'}],
            'XOO':[{h:'XL',t:'VENT'},{h:'FIX',t:'FIX'},{h:'FIX',t:'FIX'}],
            'XOX':[{h:'XL',t:'VENT'},{h:'XFIX',t:'XFIX'},{h:'XR',t:'VENT'}],
            'OXO':[{h:'FIX',t:'FIX'},{h:'XL',t:'VENT'},{h:'FIX',t:'FIX'}],
            'XO':[{h:'XL',t:'VENT'},{h:'XFIX',t:'XFIX'}],
            'OX':[{h:'XFIX',t:'XFIX'},{h:'XR',t:'VENT'}]
        };
        var fields = [];
        var pIdx = 1;
        var i = 0, len = code.length;
        while (i < len) {
            var matched = false;
            for (var ti = 0; ti < tokens.length; ti++) {
                var tok = tokens[ti];
                if (code.substr(i, tok.length) === tok) {
                    i += tok.length;
                    // Optional count digit
                    var cnt = 1;
                    if (i < len && code[i] >= '0' && code[i] <= '9') { cnt = parseInt(code[i]); i++; }
                    // Optional H/V stack
                    if (i < len && (code[i]==='H'||code[i]==='V')) { i++; }
                    // Multi-char XO pattern — expand sub-fields
                    if (tok.length >= 2 && /^[XO]+$/.test(tok) && xoLayouts[tok]) {
                        for (var ci = 0; ci < cnt; ci++) {
                            var sl = xoLayouts[tok];
                            for (var si = 0; si < sl.length; si++) {
                                fields.push({p:pIdx++, h:sl[si].h, t:sl[si].t});
                            }
                        }
                    } else {
                        var isSash = sashTypes[tok] ? true : false;
                        for (var ci2 = 0; ci2 < cnt; ci2++) {
                            fields.push({p:pIdx++, h: isSash ? tok : 'FIX', t: isSash ? 'VENT' : 'FIX'});
                        }
                    }
                    matched = true;
                    break;
                }
            }
            if (!matched) i++;
        }
        return fields.length > 0 ? fields : null;
    }

    function parseSuffix(fullType) {
        // Returns {bottom: {count, code}, top: {count, code}}
        // code is null for numeric suffixes (B3 = 3 fixed panels)
        // code is a type string for type-code suffixes (BOXOX = OXOX layout)
        var s = (fullType||'').toUpperCase();
        var bottom = {count:0, code:null}, top = {count:0, code:null};

        // Extract suffix portion after the base type
        // Find the first dash followed by B or T
        var suffixMatch = s.match(/-((?:T\d*[XO]*\d*)?(?:B\d*[XO]*\d*)?)$/i);
        if (!suffixMatch) {
            // Try alternate: match -T... and -B... separately
            var tm = s.match(/-T([XO]{2,})/i);
            var tn = s.match(/-T(\d+)/i);
            var bm = s.match(/-B([XO]{2,})/i);
            var bn = s.match(/-B(\d+)/i);
            if (!tm && !tn && !bm && !bn) {
                // Try combined like T3B3 or T1B4
                var combo = s.match(/-T(\d+)B(\d+)/i);
                if (combo) {
                    top = {count: parseInt(combo[1]), code: null};
                    bottom = {count: parseInt(combo[2]), code: null};
                }
                return {bottom: bottom, top: top};
            }
        }

        // Type-code top: -TOXOX, -TOXXO, etc.
        var ttm = s.match(/(?:^|-)T([XO]{2,})/);
        if (ttm) {
            top = {count: ttm[1].length, code: ttm[1]};
        } else {
            // Numeric top: -T1, -T3, etc. (also handles T3B3 combo)
            var tnm = s.match(/(?:^|-)T(\d+)/);
            if (tnm) top = {count: parseInt(tnm[1]), code: null};
        }

        // Type-code bottom: -BOXOX, -BXOXO, -BOX, etc.
        var btm = s.match(/(?:^|-)B([XO]{2,})/);
        if (btm) {
            bottom = {count: btm[1].length, code: btm[1]};
        } else {
            // Numeric bottom: -B1, -B3, -B4 etc.
            var bnm = s.match(/(?:^|-)B(\d+)/);
            if (bnm) bottom = {count: parseInt(bnm[1]), code: null};
        }

        return {bottom: bottom, top: top};
    }

    // Auto-generate layout for compound/unknown types by tokenizing
    // AW2VOAW2V → [{type:'vent',dir:'up'}, {type:'fix'}, {type:'vent',dir:'up'}]  (3 columns)
    function autoLayout(code) {
        if (!code) return null;
        var tokens = [
            'OXOXO','XOXOX','OXXOO','OOXOO',
            'OXXO','XOOX','XOXO','OXOX',
            'XXO','OXX','OOX','XOO','XOX','OXO',
            'AW','CL','CR','CM','SH','DH','PW','SW',
            'XO','OX','SL','F','X','O'
        ];
        // Sub-layouts for multi-char XO patterns (each sub-panel becomes a column)
        var xoExpand = {
            'OXXO':['fix','vent-r','vent-l','fix'],
            'XOOX':['vent-l','fix','fix','vent-r'],
            'XOXO':['vent-l','fix','vent-r','fix'],
            'OXOX':['fix','vent-r','fix','vent-l'],
            'OXOXO':['fix','vent-l','fix','vent-r','fix'],
            'XOXOX':['vent-l','fix','vent-r','fix','vent-l'],
            'XXO':['vent-l','vent-r','fix'],
            'OXX':['fix','vent-l','vent-r'],
            'OOX':['fix','fix','vent-r'],
            'XOO':['vent-l','fix','fix'],
            'XOX':['vent-l','fix','vent-r'],
            'OXO':['fix','vent-l','fix'],
            'XO':['vent-l','fix'],
            'OX':['fix','vent-r']
        };
        var ventTypes = {'X':1,'AW':1,'CL':1,'CR':1,'CM':1,'SH':1,'DH':1,'SL':1,'SW':1};
        var dirMap = {'X':'right','AW':'up','CL':'left','CR':'right','CM':'left','SH':'sh','DH':'dh','SL':'right','SW':'left'};
        var columns = []; // each: {type:'fix'|'vent'|'sh', dir:...}
        var i = 0, len = code.length;
        while (i < len) {
            var matched = false;
            for (var ti = 0; ti < tokens.length; ti++) {
                var tok = tokens[ti];
                if (code.substr(i, tok.length) === tok) {
                    i += tok.length;
                    var cnt = 1;
                    if (i < len && code[i] >= '0' && code[i] <= '9') { cnt = parseInt(code[i]); i++; }
                    var isVStack = false;
                    if (i < len && code[i] === 'V') { isVStack = true; i++; }
                    else if (i < len && code[i] === 'H') { i++; }
                    // Multi-char XO patterns expand to multiple columns
                    if (xoExpand[tok]) {
                        var sub = xoExpand[tok];
                        for (var ci = 0; ci < cnt; ci++) {
                            for (var si = 0; si < sub.length; si++) {
                                var sp = sub[si].split('-');
                                columns.push({type: sp[0] === 'vent' ? 'vent' : 'fix', dir: sp[1] || null});
                            }
                        }
                    } else {
                        // Named type: V-stack = 1 column, otherwise repeat cnt columns
                        var colCount = isVStack ? 1 : cnt;
                        var isVent = ventTypes[tok] ? true : false;
                        var dir = dirMap[tok] || 'right';
                        for (var ci2 = 0; ci2 < colCount; ci2++) {
                            if (tok === 'SH' || tok === 'DH') {
                                columns.push({type:'sh', dir:null});
                            } else {
                                columns.push({type: isVent ? 'vent' : 'fix', dir: isVent ? dir : null});
                            }
                        }
                    }
                    matched = true;
                    break;
                }
            }
            if (!matched) i++;
        }
        if (columns.length === 0) return null;
        // Convert columns to layout panels with equal widths
        var n = columns.length;
        var panels = [];
        for (var pi = 0; pi < n; pi++) {
            panels.push({x: pi/n, w: 1/n, type: columns[pi].type, dir: columns[pi].dir});
        }
        return {panels: panels, hasMullion: n > 1, isSH: columns.every(function(c){return c.type==='sh';})};
    }

    // ── Shape path generator for Profile Exchange SVG ──
    // Returns {outer:'d', inner:'d'} or null if no shape / rectangular
    function getShapeFramePaths(sc, sp, ox, oy, ow, oh, f, winW, winH) {
        if (!sc) return null;
        var x1=ox, y1=oy, x2=ox+ow, y2=oy+oh;
        var cx=(x1+x2)/2, cy=(y1+y2)/2;
        var h1Ratio = (sp.H1 && parseFloat(winH)>0) ? parseFloat(sp.H1)/parseFloat(winH) : 0.5;
        var w1Ratio = (sp.W1 && parseFloat(winW)>0) ? parseFloat(sp.W1)/parseFloat(winW) : 0.25;

        // Helper: polygon inset (mirrors PHP $_insetPoly)
        function insetPoly(pts, f) {
            var n=pts.length; if(n<3)return pts;
            var area=0;
            for(var i=0;i<n;i++){var j=(i+1)%n; area+=pts[i][0]*pts[j][1]-pts[j][0]*pts[i][1];}
            var sign=area>0?1:-1;
            var lines=[];
            for(var i=0;i<n;i++){
                var j=(i+1)%n;
                var dx=pts[j][0]-pts[i][0], dy=pts[j][1]-pts[i][1];
                var len=Math.sqrt(dx*dx+dy*dy);
                if(len<0.001)continue;
                var nx=-sign*dy/len, ny=sign*dx/len;
                lines.push({px:pts[i][0]+f*nx, py:pts[i][1]+f*ny, dx:dx, dy:dy});
            }
            var inner=[], m=lines.length;
            for(var i=0;i<m;i++){
                var j2=(i+1)%m;
                var l1=lines[i], l2=lines[j2];
                var det=l1.dx*l2.dy-l1.dy*l2.dx;
                if(Math.abs(det)<0.0001)continue;
                var t=((l2.px-l1.px)*l2.dy-(l2.py-l1.py)*l2.dx)/det;
                inner.push([Math.round((l1.px+t*l1.dx)*100)/100, Math.round((l1.py+t*l1.dy)*100)/100]);
            }
            return inner;
        }
        function polyToD(pts){
            if(!pts.length)return '';
            var d='M'+pts[0][0]+','+pts[0][1];
            for(var i=1;i<pts.length;i++) d+=' L'+pts[i][0]+','+pts[i][1];
            return d+' Z';
        }

        var outerPts=null, outerD=null, innerD=null;

        if(sc.indexOf('HALF_ROUND')>=0||sc.indexOf('HALFRND')>=0||sc==='M1'||sc==='S03'||sc==='S49'){
            var r=(x2-x1)/2, archH=Math.min(r,(y2-y1)*0.45), archY=y1+archH;
            outerD='M'+x1+','+y2+' L'+x1+','+archY+' A'+r+','+archH+' 0 0,1 '+x2+','+archY+' L'+x2+','+y2+' Z';
            var ir=r-f, iah=Math.max(1,archH-f), iay=y1+f+iah;
            innerD='M'+(x1+f)+','+(y2-f)+' L'+(x1+f)+','+iay+' A'+ir+','+iah+' 0 0,1 '+(x2-f)+','+iay+' L'+(x2-f)+','+(y2-f)+' Z';
        } else if(sc.indexOf('ARCH')>=0||sc==='M2'||sc==='M5'){
            var r=(x2-x1)/2, archY=y1+r;
            outerD='M'+x1+','+y2+' L'+x1+','+archY+' A'+r+','+r+' 0 0,1 '+x2+','+archY+' L'+x2+','+y2+' Z';
            var ir=r-f, iay=y1+f+ir;
            innerD='M'+(x1+f)+','+(y2-f)+' L'+(x1+f)+','+iay+' A'+ir+','+ir+' 0 0,1 '+(x2-f)+','+iay+' L'+(x2-f)+','+(y2-f)+' Z';
        } else if(sc.indexOf('RAKE_UP_LEFT')>=0||sc==='S15'||sc==='RAKE'){
            var h1y=y1+(y2-y1)*(1-h1Ratio);
            outerPts=[[x1,y2],[x1,y1],[x2,h1y],[x2,y2]];
        } else if(sc.indexOf('RAKE_UP_RIGHT')>=0||sc==='S17'){
            var h1y=y1+(y2-y1)*(1-h1Ratio);
            outerPts=[[x1,h1y],[x2,y1],[x2,y2],[x1,y2]];
        } else if(sc.indexOf('RAKE_DOWN_RIGHT')>=0||sc==='S23'){
            var h1y=y1+(y2-y1)*h1Ratio;
            outerPts=[[x1,y1],[x2,y1],[x2,y2],[x1,h1y]];
        } else if(sc.indexOf('RAKE_DOWN_LEFT')>=0||sc==='S25'){
            var h1y=y1+(y2-y1)*h1Ratio;
            outerPts=[[x1,y1],[x2,y1],[x2,h1y],[x1,y2]];
        } else if(sc.indexOf('RAKE_RIGHT_TOP')>=0||sc==='S27'){
            var w1x=x2-(x2-x1)*h1Ratio;
            outerPts=[[x1,y1],[x2,y1],[x2,y2],[w1x,y2]];
        } else if(sc.indexOf('RAKE_LEFT_TOP')>=0||sc==='S29'){
            var w1x=x1+(x2-x1)*h1Ratio;
            outerPts=[[x1,y1],[x2,y1],[w1x,y2],[x1,y2]];
        } else if(sc.indexOf('TRI')>=0||sc.indexOf('TRIANGLE')>=0){
            outerPts=[[x1,y2],[cx,y1],[x2,y2]];
        } else if(sc.indexOf('OCT')>=0||sc.indexOf('OCTAGON')>=0){
            var ins=(x2-x1)*0.29;
            outerPts=[[x1+ins,y1],[x2-ins,y1],[x2,y1+ins],[x2,y2-ins],[x2-ins,y2],[x1+ins,y2],[x1,y2-ins],[x1,y1+ins]];
        } else if(sc.indexOf('QUARTER')>=0||sc.indexOf('QTR')>=0||sc==='S61'||sc==='S62'){
            var r=Math.min(x2-x1,y2-y1);
            outerD='M'+x1+','+y2+' L'+x1+','+(y2-r)+' A'+r+','+r+' 0 0,1 '+(x1+r)+','+y2+' Z';
            var ir=r-f;
            var meetY=y2-Math.sqrt(Math.max(0,ir*ir-f*f));
            var meetX=x1+Math.sqrt(Math.max(0,ir*ir-f*f));
            innerD='M'+(x1+f)+','+(y2-f)+' L'+(x1+f)+','+meetY+' A'+ir+','+ir+' 0 0,1 '+meetX+','+(y2-f)+' Z';
        } else if(sc.indexOf('CIRCLE')>=0||sc==='S48'){
            var rx=(x2-x1)/2, ry=(y2-y1)/2;
            outerD='M'+cx+','+y1+' A'+rx+','+ry+' 0 1,1 '+cx+','+y2+' A'+rx+','+ry+' 0 1,1 '+cx+','+y1;
            var irx=rx-f, iry=ry-f;
            innerD='M'+cx+','+(cy-iry)+' A'+irx+','+iry+' 0 1,1 '+cx+','+(cy+iry)+' A'+irx+','+iry+' 0 1,1 '+cx+','+(cy-iry);
        } else if(sc.indexOf('TRAP')>=0||sc.indexOf('TRAPEZ')>=0){
            var ins=(x2-x1)*w1Ratio;
            outerPts=[[x1,y2],[x1+ins,y1],[x2-ins,y1],[x2,y2]];
        } else if(sc.indexOf('PEAK')>=0){
            var peakH=(y2-y1)*h1Ratio;
            outerPts=[[x1,y2],[x1,y1+peakH],[cx,y1],[x2,y1+peakH],[x2,y2]];
        } else if(sc.indexOf('CLIP_LT')>=0||sc==='S04'){
            var cw=(x2-x1)*w1Ratio, ch=(y2-y1)*h1Ratio;
            outerPts=[[x1+cw,y1],[x2,y1],[x2,y2],[x1,y2],[x1,y1+ch]];
        } else if(sc.indexOf('CLIP_RT')>=0||sc==='S06'){
            var cw=(x2-x1)*w1Ratio, ch=(y2-y1)*h1Ratio;
            outerPts=[[x1,y1],[x2-cw,y1],[x2,y1+ch],[x2,y2],[x1,y2]];
        } else if(sc.indexOf('CLIP_RB')>=0||sc==='S09'){
            var cw=(x2-x1)*w1Ratio, ch=(y2-y1)*h1Ratio;
            outerPts=[[x1,y1],[x2,y1],[x2,y2-ch],[x2-cw,y2],[x1,y2]];
        } else if(sc.indexOf('CLIP_LB')>=0||sc==='S12'){
            var cw=(x2-x1)*w1Ratio, ch=(y2-y1)*h1Ratio;
            outerPts=[[x1,y1],[x2,y1],[x2,y2],[x1+cw,y2],[x1,y2-ch]];
        } else if(sc.indexOf('HEXAGON')>=0||sc.indexOf('HEX')>=0){
            var ins=(y2-y1)*0.25;
            outerPts=[[cx,y1],[x2,y1+ins],[x2,y2-ins],[cx,y2],[x1,y2-ins],[x1,y1+ins]];
        } else if(sc.indexOf('PENTAGON')>=0||sc.indexOf('PENT')>=0){
            var pm=y1+(y2-y1)*0.4;
            outerPts=[[x1,y2],[x1,pm],[cx,y1],[x2,pm],[x2,y2]];
        } else if(sc.indexOf('GOTHIC')>=0){
            var r2=(x2-x1)*0.7, springY=y1+(y2-y1)*0.35;
            outerD='M'+x1+','+y2+' L'+x1+','+springY+' A'+r2+','+r2+' 0 0,1 '+cx+','+y1+' A'+r2+','+r2+' 0 0,1 '+x2+','+springY+' L'+x2+','+y2+' Z';
            var ir2=r2-f;
            var halfW2=(x2-x1)/2;
            var ga=Math.atan2(springY-y1,halfW2);
            var pi2=ga>0.01?f/Math.sin(ga):f*1.5;
            pi2=Math.min(pi2,f*3);
            innerD='M'+(x1+f)+','+(y2-f)+' L'+(x1+f)+','+springY+' A'+ir2+','+ir2+' 0 0,1 '+cx+','+(y1+pi2)+' A'+ir2+','+ir2+' 0 0,1 '+(x2-f)+','+springY+' L'+(x2-f)+','+(y2-f)+' Z';
        } else if(sc.indexOf('DIAMOND')>=0||sc.indexOf('RHOMBUS')>=0){
            outerPts=[[cx,y1],[x2,cy],[cx,y2],[x1,cy]];
        } else {
            return null; // Unknown shape, use rectangular
        }

        // For polygon-based shapes, use inset helper
        if(outerPts){
            outerD=polyToD(outerPts);
            innerD=polyToD(insetPoly(outerPts, f));
        }
        return (outerD && innerD) ? {outer:outerD, inner:innerD} : null;
    }

    function renderPrexhsSvg(base, prexMap, fullType, winW, winH, shapeCode, shapeParams) {
        var el = $('csPrexhsSvg');
        if (!el) return;
        _prexSelectedKey = null;
        shapeCode = (shapeCode || '').toUpperCase().trim();
        var sp = {};
        try { sp = shapeParams ? JSON.parse(shapeParams) : {}; } catch(e) { sp = {}; }

        var layouts = {
            'XO':  { panels: [{x:0,w:0.5,type:'fix'},{x:0.5,w:0.5,type:'vent',dir:'right'}], hasMullion:true },
            'OX':  { panels: [{x:0,w:0.5,type:'vent',dir:'left'},{x:0.5,w:0.5,type:'fix'}], hasMullion:true },
            'OXXO':{ panels: [{x:0,w:0.25,type:'fix'},{x:0.25,w:0.25,type:'vent',dir:'right'},{x:0.5,w:0.25,type:'vent',dir:'left'},{x:0.75,w:0.25,type:'fix'}], hasMullion:true },
            'XOOX':{ panels: [{x:0,w:0.25,type:'vent',dir:'left'},{x:0.25,w:0.25,type:'fix'},{x:0.5,w:0.25,type:'fix'},{x:0.75,w:0.25,type:'vent',dir:'right'}], hasMullion:true },
            'XOXO':{ panels: [{x:0,w:0.25,type:'vent',dir:'left'},{x:0.25,w:0.25,type:'fix'},{x:0.5,w:0.25,type:'vent',dir:'right'},{x:0.75,w:0.25,type:'fix'}], hasMullion:true },
            'OXOX':{ panels: [{x:0,w:0.25,type:'fix'},{x:0.25,w:0.25,type:'vent',dir:'right'},{x:0.5,w:0.25,type:'fix'},{x:0.75,w:0.25,type:'vent',dir:'left'}], hasMullion:true },
            'OXOXO':{ panels: [{x:0,w:0.2,type:'fix'},{x:0.2,w:0.2,type:'vent',dir:'left'},{x:0.4,w:0.2,type:'fix'},{x:0.6,w:0.2,type:'vent',dir:'right'},{x:0.8,w:0.2,type:'fix'}], hasMullion:true },
            'XOX': { panels: [{x:0,w:0.33,type:'vent',dir:'left'},{x:0.33,w:0.34,type:'fix'},{x:0.67,w:0.33,type:'vent',dir:'right'}], hasMullion:true },
            'OOX': { panels: [{x:0,w:0.33,type:'fix'},{x:0.33,w:0.34,type:'fix'},{x:0.67,w:0.33,type:'vent',dir:'right'}], hasMullion:true },
            'XOO': { panels: [{x:0,w:0.33,type:'vent',dir:'left'},{x:0.33,w:0.34,type:'fix'},{x:0.67,w:0.33,type:'fix'}], hasMullion:true },
            'XXO': { panels: [{x:0,w:0.33,type:'vent',dir:'left'},{x:0.33,w:0.34,type:'vent',dir:'right'},{x:0.67,w:0.33,type:'fix'}], hasMullion:true },
            'OXX': { panels: [{x:0,w:0.33,type:'fix'},{x:0.33,w:0.34,type:'vent',dir:'left'},{x:0.67,w:0.33,type:'vent',dir:'right'}], hasMullion:true },
            'SH':  { panels: [{x:0,w:1,type:'sh'}], isSH:true },
            'SHSH':{ panels: [{x:0,w:0.5,type:'sh'},{x:0.5,w:0.5,type:'sh'}], isSH:true, hasMullion:true },
            'SHOSH':{ panels: [{x:0,w:0.33,type:'sh'},{x:0.33,w:0.34,type:'fix'},{x:0.67,w:0.33,type:'sh'}], hasMullion:true },
            'PW':  { panels: [{x:0,w:1,type:'fix'}] },
            'CL':  { panels: [{x:0,w:1,type:'vent',dir:'left'}] },
            'CR':  { panels: [{x:0,w:1,type:'vent',dir:'right'}] },
            'CLCR':{ panels: [{x:0,w:0.5,type:'vent',dir:'left'},{x:0.5,w:0.5,type:'vent',dir:'right'}], hasMullion:true },
            'AW':  { panels: [{x:0,w:1,type:'vent',dir:'up'}] }
        };

        // Try known layout first, then auto-generate from compound type, then PW fallback
        var lay = layouts[base] || autoLayout(fullType || base) || autoLayout(base) || layouts['PW'];

        // ── Dimensions (proportional to actual window, capped for sidebar) ──
        var svgW = 180;
        // Compute proportional height from actual window dims (if available)
        var aspectRatio = (winW && winH && parseFloat(winW) > 0 && parseFloat(winH) > 0)
            ? parseFloat(winH) / parseFloat(winW)
            : 0.65;
        // Clamp aspect ratio between 0.3 (very wide) and 1.2 (very tall)
        aspectRatio = Math.max(0.3, Math.min(1.2, aspectRatio));
        var svgH = Math.round(svgW * aspectRatio) + 16; // +16 for field number labels
        var fOuter = 6;    // outer frame thickness
        var fInner = 2;    // inner frame line
        var sashW  = 3;    // sash rail thickness
        var mullW  = 4;    // mullion thickness
        var pad    = 4;

        // Glass area bounds (inside frame)
        var gx = pad + fOuter + fInner;
        var gy = pad + fOuter + fInner;
        var gw = svgW - 2*gx;
        var gh = svgH - 2*gy - 12; // reserve space for field numbers

        var ft = prexMap['FRAME TOP'] || '';
        var fb = prexMap['FRAME BOTTOM'] || '';
        var fl = prexMap['FRAME LEFT'] || '';
        var fr = prexMap['FRAME RIGHT'] || '';
        var mr = prexMap['MULLION MEETING RAIL'] || prexMap['HEAVY DUTY MULLION'] || '';
        var sash = prexMap['HS SASH'] || '';
        var intlk = prexMap['SASH HINGE SIDE'] || '';

        var s = '<svg viewBox="0 0 '+svgW+' '+svgH+'" xmlns="http://www.w3.org/2000/svg" style="width:100%;max-width:'+svgW+'px;display:block;margin:0 auto;background:#fff">';

        // ═══ Outer frame ═══
        var ox = pad, oy = pad;
        var ow = svgW - 2*pad, oh = svgH - 2*pad - 12;

        // ── Shape-aware frame rendering ──
        var _shapePaths = getShapeFramePaths(shapeCode, sp, ox, oy, ow, oh, fOuter, winW, winH);
        var _hasShape = _shapePaths !== null;

        if (_hasShape) {
            // Draw shaped frame: outer path, inner path, frame fill between them
            s += '<path d="'+_shapePaths.outer+'" fill="#dbeafe" fill-opacity="0.5" stroke="#64748b" stroke-width="1.5" stroke-linejoin="round"/>';
            s += '<path d="'+_shapePaths.inner+'" fill="#fff" stroke="#64748b" stroke-width="1" stroke-linejoin="round"/>';
            // Single clickable frame overlay for shaped windows
            s += '<path id="prex-frame-top" data-side="top" d="'+_shapePaths.outer+'" fill="transparent" style="cursor:pointer"/>';
            // Frame label in center of frame band
            s += '<text x="'+(svgW/2)+'" y="'+(oy+fOuter/2+1)+'" fill="#1e40af" font-size="7" font-family="system-ui,sans-serif" font-weight="700" text-anchor="middle" dominant-baseline="central" pointer-events="none">'+ft+'</text>';
        } else {
            // Standard rectangular frame (double-line like CAD)
            s += '<rect x="'+ox+'" y="'+oy+'" width="'+ow+'" height="'+oh+'" fill="none" stroke="#64748b" stroke-width="1.5" rx="1"/>';
            s += '<rect x="'+(ox+fOuter)+'" y="'+(oy+fOuter)+'" width="'+(ow-2*fOuter)+'" height="'+(oh-2*fOuter)+'" fill="none" stroke="#64748b" stroke-width="1" rx="1"/>';

            // ── Clickable frame side overlays (transparent, for click detection) ──
            // Top
            s += '<rect id="prex-frame-top" data-side="top" x="'+ox+'" y="'+oy+'" width="'+ow+'" height="'+fOuter+'" fill="#dbeafe" opacity="0.5" style="cursor:pointer" rx="1"/>';
            s += '<text id="prex-label-top" x="'+(svgW/2)+'" y="'+(oy+fOuter/2)+'" fill="#1e40af" font-size="7" font-family="system-ui,sans-serif" font-weight="700" text-anchor="middle" dominant-baseline="central" pointer-events="none">'+ft+'</text>';
            // Bottom
            var botFrameY = oy + oh - fOuter;
            s += '<rect id="prex-frame-bottom" data-side="bottom" x="'+ox+'" y="'+botFrameY+'" width="'+ow+'" height="'+fOuter+'" fill="#dbeafe" opacity="0.5" style="cursor:pointer" rx="1"/>';
            s += '<text id="prex-label-bottom" x="'+(svgW/2)+'" y="'+(botFrameY+fOuter/2)+'" fill="#1e40af" font-size="7" font-family="system-ui,sans-serif" font-weight="700" text-anchor="middle" dominant-baseline="central" pointer-events="none">'+fb+'</text>';
            // Left
            s += '<rect id="prex-frame-left" data-side="left" x="'+ox+'" y="'+(oy+fOuter)+'" width="'+fOuter+'" height="'+(oh-2*fOuter)+'" fill="#dbeafe" opacity="0.5" style="cursor:pointer"/>';
            s += '<text id="prex-label-left" x="'+(ox+fOuter/2)+'" y="'+(oy+oh/2)+'" fill="#1e40af" font-size="7" font-family="system-ui,sans-serif" font-weight="700" text-anchor="middle" dominant-baseline="central" pointer-events="none" transform="rotate(-90,'+(ox+fOuter/2)+','+(oy+oh/2)+')">'+fl+'</text>';
            // Right
            var rightFrameX = ox + ow - fOuter;
            s += '<rect id="prex-frame-right" data-side="right" x="'+rightFrameX+'" y="'+(oy+fOuter)+'" width="'+fOuter+'" height="'+(oh-2*fOuter)+'" fill="#dbeafe" opacity="0.5" style="cursor:pointer"/>';
            s += '<text id="prex-label-right" x="'+(rightFrameX+fOuter/2)+'" y="'+(oy+oh/2)+'" fill="#1e40af" font-size="7" font-family="system-ui,sans-serif" font-weight="700" text-anchor="middle" dominant-baseline="central" pointer-events="none" transform="rotate(-90,'+(rightFrameX+fOuter/2)+','+(oy+oh/2)+')">'+fr+'</text>';
        }

        // ═══ Parse B/T suffix for stacked sections ═══
        var suf = parseSuffix(fullType || base);
        var topCount = suf.top.count, topCode = suf.top.code;
        var botCount = suf.bottom.count, botCode = suf.bottom.code;
        var hMullH = 4; // horizontal mullion height between sections

        // Build section list: [{lay, sy, sh, fieldStart}, ...]
        var sections = [];
        var totalSections = 1 + (topCount > 0 ? 1 : 0) + (botCount > 0 ? 1 : 0);
        var hMullTotal = (totalSections - 1) * hMullH;
        var sectionH = (gh - hMullTotal) / totalSections;
        var curY = gy;
        var fieldIdx = 1;

        // Top section
        if (topCount > 0) {
            var topLay = topCode ? (autoLayout(topCode) || {panels:[{x:0,w:1,type:'fix'}],hasMullion:false}) : null;
            if (!topLay) { var tp=[]; for(var ti=0;ti<topCount;ti++) tp.push({x:ti/topCount,w:1/topCount,type:'fix'}); topLay={panels:tp,hasMullion:topCount>1}; }
            sections.push({lay:topLay, sy:curY, sh:sectionH, fieldStart:fieldIdx, label:'T'});
            fieldIdx += topLay.panels.length;
            curY += sectionH + hMullH;
        }
        // Main section
        sections.push({lay:lay, sy:curY, sh: totalSections===1 ? gh : sectionH, fieldStart:fieldIdx, label:'M'});
        fieldIdx += lay.panels.length;
        curY += (totalSections===1 ? gh : sectionH) + hMullH;
        // Bottom section
        if (botCount > 0) {
            var botLay = botCode ? (autoLayout(botCode) || {panels:[{x:0,w:1,type:'fix'}],hasMullion:false}) : null;
            if (!botLay) { var bp=[]; for(var bi=0;bi<botCount;bi++) bp.push({x:bi/botCount,w:1/botCount,type:'fix'}); botLay={panels:bp,hasMullion:botCount>1}; }
            sections.push({lay:botLay, sy:curY, sh:sectionH, fieldStart:fieldIdx, label:'B'});
            fieldIdx += botLay.panels.length;
        }

        // ═══ Clip path for shaped windows ═══
        if (_hasShape) {
            s += '<defs><clipPath id="prex-shape-clip"><path d="'+_shapePaths.inner+'"/></clipPath></defs>';
        }

        // ═══ Draw panels for each section ═══
        var glassColor = '#d4e4f7';
        if (_hasShape) s += '<g clip-path="url(#prex-shape-clip)">';

        // Helper: draw one panel
        function drawPanel(px, py, pw, ph, p) {
            if (p.type === 'sh') {
                var uH = ph * 0.48, lH = ph * 0.48, rY = py + uH;
                s += '<rect x="'+px+'" y="'+py+'" width="'+pw+'" height="'+uH+'" fill="'+glassColor+'" stroke="#94a3b8" stroke-width="0.8"/>';
                s += '<rect class="prex-divider prex-mullion-el" x="'+px+'" y="'+rY+'" width="'+pw+'" height="'+(ph-uH-lH)+'" fill="#94a3b8" stroke="#64748b" stroke-width="0.5" style="cursor:pointer"/>';
                s += '<rect class="prex-vent-panel prex-sash-el" x="'+(px+2)+'" y="'+(rY+ph-uH-lH)+'" width="'+(pw-4)+'" height="'+lH+'" fill="'+glassColor+'" stroke="#64748b" stroke-width="1.5" style="cursor:pointer"/>';
            } else if (p.type === 'vent') {
                s += '<rect class="prex-vent-panel prex-sash-el" x="'+px+'" y="'+py+'" width="'+pw+'" height="'+ph+'" fill="'+glassColor+'" stroke="#64748b" stroke-width="1.5" style="cursor:pointer"/>';
                s += '<rect x="'+(px+sashW)+'" y="'+(py+sashW)+'" width="'+(pw-2*sashW)+'" height="'+(ph-2*sashW)+'" fill="none" stroke="#94a3b8" stroke-width="0.8"/>';
                if (p.dir === 'up') {
                    var cx2 = px+pw/2, cy2 = py+ph;
                    s += '<line x1="'+px+'" y1="'+py+'" x2="'+cx2+'" y2="'+cy2+'" stroke="#64748b" stroke-width="0.6" stroke-dasharray="4,2"/>';
                    s += '<line x1="'+(px+pw)+'" y1="'+py+'" x2="'+cx2+'" y2="'+cy2+'" stroke="#64748b" stroke-width="0.6" stroke-dasharray="4,2"/>';
                } else if (p.dir === 'right' || p.dir === 'left') {
                    var aY = py+ph*0.45, aX1 = px+pw*0.15, aX2 = px+pw*0.85;
                    s += '<line x1="'+aX1+'" y1="'+aY+'" x2="'+aX2+'" y2="'+aY+'" stroke="#64748b" stroke-width="0.8" stroke-dasharray="4,2"/>';
                    if (p.dir==='right') s += '<polygon points="'+aX2+','+aY+' '+(aX2-5)+','+(aY-3)+' '+(aX2-5)+','+(aY+3)+'" fill="#64748b"/>';
                    else s += '<polygon points="'+aX1+','+aY+' '+(aX1+5)+','+(aY-3)+' '+(aX1+5)+','+(aY+3)+'" fill="#64748b"/>';
                }
            } else {
                s += '<rect x="'+px+'" y="'+py+'" width="'+pw+'" height="'+ph+'" fill="'+glassColor+'" stroke="#94a3b8" stroke-width="0.8"/>';
            }
        }

        var _fieldCounter = 1;
        sections.forEach(function(sec) {
            var sLay = sec.lay;
            var nPanels = sLay.panels.length;
            sLay.panels.forEach(function(p, i) {
                var px = gx + p.x * gw;
                var pw = p.w * gw;
                if (sLay.hasMullion && nPanels > 1) {
                    var mG = mullW / 2;
                    if (i === 0) pw -= mG;
                    else if (i === nPanels - 1) { px += mG; pw -= mG; }
                    else { px += mG; pw -= mullW; }
                }
                s += '<g class="prex-field-group" data-field-idx="'+_fieldCounter+'" style="cursor:pointer">';
                drawPanel(px, sec.sy, pw, sec.sh, p);
                s += '</g>';
                _fieldCounter++;
            });
            // Vertical mullions within this section
            if (sLay.hasMullion && nPanels > 1) {
                for (var mi = 1; mi < nPanels; mi++) {
                    var mPos = sLay.panels[mi];
                    var mx = gx + mPos.x * gw - mullW/2;
                    s += '<rect class="prex-divider prex-mullion-el" x="'+mx+'" y="'+sec.sy+'" width="'+mullW+'" height="'+sec.sh+'" fill="#b0b8c4" stroke="#64748b" stroke-width="0.8" style="cursor:pointer"/>';
                }
            }
        });

        // ═══ Horizontal mullions between sections ═══
        if (sections.length > 1) {
            for (var si = 0; si < sections.length - 1; si++) {
                var hmy = sections[si].sy + sections[si].sh;
                s += '<rect class="prex-divider prex-mullion-el" x="'+gx+'" y="'+hmy+'" width="'+gw+'" height="'+hMullH+'" fill="#b0b8c4" stroke="#64748b" stroke-width="0.5" style="cursor:pointer"/>';
            }
        }

        // Close shape clip group
        if (_hasShape) s += '</g>';

        // ═══ Field numbers below the diagram ═══
        var allFieldIdx = 1;
        sections.forEach(function(sec) {
            var nP = sec.lay.panels.length;
            sec.lay.panels.forEach(function(p, i) {
                var px = gx + p.x * gw;
                var pw = p.w * gw;
                if (sec.lay.hasMullion && nP > 1) {
                    var mG2 = mullW/2;
                    if (i===0) pw -= mG2;
                    else if (i===nP-1) { px += mG2; pw -= mG2; }
                    else { px += mG2; pw -= mullW; }
                }
                // Only show field numbers for the widest (main or only) section to avoid clutter
                if (sections.length === 1 || sec.label === 'M') {
                    s += '<text x="'+(px+pw/2)+'" y="'+(oy+oh+10)+'" fill="#475569" font-size="9" font-family="system-ui,sans-serif" font-weight="700" text-anchor="middle" dominant-baseline="central">'+allFieldIdx+'</text>';
                }
                allFieldIdx++;
            });
        });

        // ═══ Interlock zone indicator ═══
        var mainSec = sections.find(function(ss){return ss.label==='M';}) || sections[0];
        if (intlk && mainSec.lay.hasMullion && mainSec.lay.panels.length > 1) {
            for (var ii = 1; ii < mainSec.lay.panels.length; ii++) {
                var iPos = mainSec.lay.panels[ii];
                var ix = gx + iPos.x * gw;
                s += '<line x1="'+(ix-8)+'" y1="'+(mainSec.sy+3)+'" x2="'+(ix+8)+'" y2="'+(mainSec.sy+3)+'" stroke="#be185d" stroke-width="1" stroke-dasharray="2,2"/>';
                s += '<line x1="'+(ix-8)+'" y1="'+(mainSec.sy+mainSec.sh-3)+'" x2="'+(ix+8)+'" y2="'+(mainSec.sy+mainSec.sh-3)+'" stroke="#be185d" stroke-width="1" stroke-dasharray="2,2"/>';
            }
        }

        s += '</svg>';
        el.innerHTML = s;

        // ── SVG side → PREXHS key mapping ──
        var _sideToKey = {top:'FRAME TOP', bottom:'FRAME BOTTOM', left:'FRAME LEFT', right:'FRAME RIGHT'};

        // ── Event delegation for SVG clicks ──
        el.onclick = function(e) {
            var tgt = e.target;
            if (!tgt || !tgt.getAttribute) return;
            var side = tgt.getAttribute('data-side');
            if (side && _sideToKey[side]) { _prexHighlight(_sideToKey[side]); return; }
            if (tgt.classList && tgt.classList.contains('prex-sash-el')) { _prexHighlight('HS SASH'); return; }
            if (tgt.classList && tgt.classList.contains('prex-mullion-el')) { _prexHighlight('MULLION MEETING RAIL'); return; }
            // Click on a field panel → highlight corresponding measurement row
            var fieldGroup = tgt.closest('.prex-field-group[data-field-idx]');
            if (fieldGroup) {
                var fIdx = parseInt(fieldGroup.getAttribute('data-field-idx'));
                highlightPrexhsField(fIdx);
                // Also highlight the measurement row
                var gfEl = document.getElementById('csGlassFields');
                if (gfEl) {
                    gfEl.querySelectorAll('.cs-field-measure-row').forEach(function(r){r.style.background='';});
                    var targetRow = gfEl.querySelector('.cs-field-measure-row[data-field-idx="'+fIdx+'"]');
                    if (targetRow) { targetRow.style.background='#dbeafe'; targetRow.scrollIntoView({block:'nearest'}); }
                }
            }
        };

        // ── Event delegation for text row clicks ──
        var prexList = $('csPrexhs');
        if (prexList) {
            prexList.onclick = function(e) {
                var row = e.target.closest('.cs-prex-row[data-prex-key]');
                if (row) {
                    var key = row.getAttribute('data-prex-key');
                    if (key) _prexHighlight(key);
                }
            };
        }
    }

    // Highlight a field panel in the PREXHS sketch by field index (1-based)
    function highlightPrexhsField(idx) {
        var svgEl = $('csPrexhsSvg');
        if (!svgEl) return;
        // Reset all field groups
        svgEl.querySelectorAll('.prex-field-group').forEach(function(g) {
            g.querySelectorAll('rect').forEach(function(r) { r.removeAttribute('filter'); });
            g.style.opacity = '1';
        });
        // Find and highlight the target field
        var target = svgEl.querySelector('.prex-field-group[data-field-idx="'+idx+'"]');
        if (!target) return;
        // Dim other fields
        svgEl.querySelectorAll('.prex-field-group').forEach(function(g) {
            if (g !== target) g.style.opacity = '0.35';
        });
        // Bright highlight on target
        target.style.opacity = '1';
        target.querySelectorAll('rect').forEach(function(r) {
            var oldStroke = r.getAttribute('stroke');
            if (oldStroke) { r.setAttribute('stroke', '#2563eb'); r.setAttribute('stroke-width', '2.5'); }
        });
        // Auto-clear after 3 seconds
        setTimeout(function(){
            svgEl.querySelectorAll('.prex-field-group').forEach(function(g) {
                g.style.opacity = '1';
                g.querySelectorAll('rect').forEach(function(r) {
                    if (r.getAttribute('stroke') === '#2563eb') {
                        r.setAttribute('stroke', r.classList.contains('prex-vent-panel') ? '#64748b' : '#94a3b8');
                        r.setAttribute('stroke-width', r.classList.contains('prex-vent-panel') ? '1.5' : '0.8');
                    }
                });
            });
            // Clear row highlight too
            var gfEl = document.getElementById('csGlassFields');
            if (gfEl) gfEl.querySelectorAll('.cs-field-measure-row').forEach(function(r){r.style.background='';});
        }, 3000);
    }

    function parseFields(type){
        var upper = type.toUpperCase().replace(/^(DYNAMIC-|PRESTIGE-|IM-|GS-|GX-|GSCO-)/,'');
        // Extract B/T suffix counts before stripping
        var suf = parseSuffix(upper);
        var base = upper.replace(/-(B[XO]+|T[XO]+|B\d|T\d|BA|TA|T\dB\d|T\dB[XO]+).*$/,'').replace(/-.*$/,'');
        var M={
            'XO':[{p:1,h:'XR',t:'VENT'},{p:2,h:'XFIX',t:'XFIX'}],
            'OX':[{p:1,h:'XFIX',t:'XFIX'},{p:2,h:'XL',t:'VENT'}],
            'OXXO':[{p:1,h:'FIX',t:'FIX'},{p:2,h:'XR',t:'VENT'},{p:3,h:'XL',t:'VENT'},{p:4,h:'FIX',t:'FIX'}],
            'XOOX':[{p:1,h:'XL',t:'VENT'},{p:2,h:'FIX',t:'FIX'},{p:3,h:'FIX',t:'FIX'},{p:4,h:'XR',t:'VENT'}],
            'XOXO':[{p:1,h:'XL',t:'VENT'},{p:2,h:'FIX',t:'FIX'},{p:3,h:'XR',t:'VENT'},{p:4,h:'FIX',t:'FIX'}],
            'OXOX':[{p:1,h:'FIX',t:'FIX'},{p:2,h:'XR',t:'VENT'},{p:3,h:'FIX',t:'FIX'},{p:4,h:'XL',t:'VENT'}],
            'OXOXO':[{p:1,h:'FIX',t:'FIX'},{p:2,h:'XL',t:'VENT'},{p:3,h:'XFIX',t:'XFIX'},{p:4,h:'XR',t:'VENT'},{p:5,h:'FIX',t:'FIX'}],
            'XXO':[{p:1,h:'XL',t:'VENT'},{p:2,h:'XR',t:'VENT'},{p:3,h:'FIX',t:'FIX'}],
            'OXX':[{p:1,h:'FIX',t:'FIX'},{p:2,h:'XL',t:'VENT'},{p:3,h:'XR',t:'VENT'}],
            'OOX':[{p:1,h:'FIX',t:'FIX'},{p:2,h:'XFIX',t:'XFIX'},{p:3,h:'XR',t:'VENT'}],
            'XOO':[{p:1,h:'XL',t:'VENT'},{p:2,h:'XFIX',t:'XFIX'},{p:3,h:'FIX',t:'FIX'}],
            'XOX':[{p:1,h:'XL',t:'VENT'},{p:2,h:'XFIX',t:'XFIX'},{p:3,h:'XR',t:'VENT'}],
            'SH':[{p:1,h:'XFIX',t:'XFIX'},{p:2,h:'XU',t:'VENT'}],
            'SHSH':[{p:1,h:'XFIX',t:'XFIX'},{p:2,h:'XU',t:'VENT'},{p:3,h:'XFIX',t:'XFIX'},{p:4,h:'XU',t:'VENT'}],
            'PW':[{p:1,h:'FIX',t:'FIX'}],
            'SHOSH':[{p:1,h:'VT',t:'VENT'},{p:2,h:'FIX',t:'FIX'},{p:3,h:'VT',t:'VENT'}],
            'CL':[{p:1,h:'CL',t:'VENT'}],'CR':[{p:1,h:'CR',t:'VENT'}],
            'CLCR':[{p:1,h:'CL',t:'VENT'},{p:2,h:'CR',t:'VENT'}],
            'AW':[{p:1,h:'AW',t:'VENT'}]
        };
        var baseFields = M[base] || autoParseFields(base) || [{p:1,h:'FIX',t:'FIX'}];
        var fields = [];
        var nextP = 1;

        // Helper: get fields for a type code section
        function sectionFields(code, count) {
            if (code) {
                var sf = M[code] || autoParseFields(code);
                if (sf) return sf;
            }
            // Numeric: N fixed panels
            var arr = [];
            for (var si = 0; si < count; si++) arr.push({p:0, h:'FIX', t:'FIX'});
            return arr;
        }

        // Top transom fields first (matches PHP buildGlassFields order)
        var topFields = sectionFields(suf.top.code, suf.top.count);
        for (var ti = 0; ti < topFields.length; ti++) {
            fields.push({p: nextP++, h: topFields[ti].h, t: topFields[ti].t});
        }
        // Then main panel fields
        for (var mi = 0; mi < baseFields.length; mi++) {
            fields.push({p: nextP++, h: baseFields[mi].h, t: baseFields[mi].t});
        }
        // Then bottom fields
        var botFields = sectionFields(suf.bottom.code, suf.bottom.count);
        for (var bi = 0; bi < botFields.length; bi++) {
            fields.push({p: nextP++, h: botFields[bi].h, t: botFields[bi].t});
        }

        return fields;
    }
})();
</script>
@endpush