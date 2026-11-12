<div class="content-wrapper">
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header ptbnull">
                        <h3 class="box-title titlefix"> <?php echo $this->lang->line('transport_routes'); ?></h3>
                        <div class="box-tools pull-right">
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-lg-12 col-md-12 col-sm-12">
                                <div class="row">
                              
                                    <div class="col-lg-2 col-md-2 col-sm-3">
                                        
                                        <?php if($listroute['vehicle_photo']){ ?>
                                        
                                           <img class="route-bus-icon" src="<?php echo base_url();?>/uploads/vehicle_photo/<?php echo $listroute['vehicle_photo']; ?>" alt="User profile picture">
                                            
                                        <?php }else{ ?>
                                        
                                            <div class="route-bus-icon"><i class="fa fa-bus"></i></div>
                                            
                                        <?php } ?>             
                                        
                                    </div>
                                    
                                    <div class="col-lg-10 col-md-10 col-sm-9">
                                        <h4 class="mt0"><b><?php echo $this->lang->line('route_title'); ?>: <?php echo $listroute['route_title']; ?></b></h4>
                                        <div class="row">
                                            <div class="col-lg-4 col-md-4 col-sm-4">
                                                <div class="route-text"><b><?php echo $this->lang->line('vehicle_number'); ?>: </b><span><?php echo $listroute['vehicle_no']; ?></span></div>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-4">
                                                <div class="route-text"><b><?php echo $this->lang->line('vehicle_model'); ?>: </b><span><?php echo $listroute['vehicle_model']; ?></span></div>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-4">
                                                <div class="route-text"><b><?php echo $this->lang->line('made'); ?>: </b><span><?php echo $listroute['manufacture_year']; ?></span></div>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-4">
                                                <div class="route-text"><b><?php echo $this->lang->line('driver_name'); ?>: </b><span><?php echo $listroute['driver_name']; ?></span></div>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-4">
                                                <div class="route-text"><b><?php echo $this->lang->line('driver_license'); ?>: </b><span><?php echo $listroute['driver_licence']; ?></span></div>
                                            </div>
                                            <div class="col-lg-4 col-md-4 col-sm-4">
                                                <div class="route-text"><b><?php echo $this->lang->line('driver_contact'); ?>: </b><span><?php echo $listroute['driver_contact']; ?></span></div>
                                            </div>
                                            <?php if (!empty($listroute['vehicle_documents'])) { ?>
                                            <div class="col-lg-4 col-md-4 col-sm-4" style="margin-top:8px;">
                                                <div class="route-text">
                                                    <b><?php echo $this->lang->line('documents'); ?>: </b>
                                                    <a href="javascript:void(0)" onclick="openDocViewer('<?php echo $this->media_storage->getImageURL('/uploads/vehicle_documents/'.$listroute['vehicle_documents']); ?>', '<?php echo htmlspecialchars($listroute['vehicle_documents']); ?>')" class="btn btn-xs btn-default" style="margin-left:5px;"><i class="fa fa-file-pdf-o"></i> View Document</a>
                                                </div>
                                            </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="route-wrap">
                                    <h4 class="title-route-h4"><b><?php echo $this->lang->line('pickup_point_list'); ?></b></h4>
                                    <section class="timeline-route">
                                        <ol>
                                        <?php
if (!empty($listroute['pickup_point'])) {
    foreach ($listroute['pickup_point'] as $pickup_point_value) {
        if ($listroute['pickup_point_name'] == $pickup_point_value['pickup_point']) {
            $class = 'active';
        } else {
            $class = '';
        }
        ?>
                                        <li class="<?php echo $class; ?>">
                                            <div class="<?php echo $class; ?>">
                                            <h4 class="timeline-title"><?php echo $pickup_point_value['pickup_point']; ?></h4>
                                            <p><i class="fa fa-tachometer timeline-icon-width"></i> <?php echo $this->lang->line('distance_km'); ?>: <?php echo $pickup_point_value['destination_distance']; ?></p>
                                            <p><i class="fa fa-clock-o timeline-icon-width"></i> <?php echo $this->lang->line('pickup_time'); ?>: <?php echo $this->customlib->timeFormat($pickup_point_value['pickup_time'], $this->customlib->getSchoolTimeFormat()); ?></p>
                                            </div>
                                        </li>
                                    <?php }}?>

                                        <li></li>
                                        </ol>

                                    <div class="arrows">
                                        <button class="arrow arrow__prev disabled" disabled><i class="fa fa-angle-left"></i></button>
                                        <button class="arrow arrow__next"><i class="fa fa-angle-right"></i></button>
                                    </div>
                                    </section>
                                </div>
                            </div>
                        </div><!--./row-->
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Document Viewer Modal -->
<div class="modal fade" id="docViewerModal" role="dialog" aria-labelledby="docViewerLabel">
    <div class="modal-dialog" role="document" style="width:92%;max-width:1050px;">
        <div class="modal-content" style="border-radius:10px;overflow:hidden;border:none;">
            <div class="modal-header" style="background:linear-gradient(135deg,#635BFF 0%,#5c54ec 100%);padding:14px 20px;border-bottom:none;display:flex;align-items:center;justify-content:space-between;">
                <h4 class="modal-title" id="docViewerLabel" style="color:#fff;font-weight:600;font-size:17px;margin:0;"><i class="fa fa-file-pdf-o" style="margin-right:8px;"></i> Vehicle Document</h4>
                <div style="display:flex;align-items:center;gap:10px;">
                    <a id="docViewerDownload" href="#" target="_blank" class="btn btn-sm" style="background:rgba(255,255,255,0.2);color:#fff;border:1px solid rgba(255,255,255,0.5);border-radius:6px;padding:5px 14px;"><i class="fa fa-download"></i> Download</a>
                    <button type="button" class="close" data-dismiss="modal" style="color:#fff;opacity:1;font-size:24px;text-shadow:none;margin:0;padding:0;line-height:1;">&times;</button>
                </div>
            </div>
            <div class="modal-body" style="padding:0;background:#2d2d2d;min-height:500px;position:relative;">
                <div id="docViewerLoading" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);color:#fff;text-align:center;z-index:10;">
                    <i class="fa fa-spinner fa-spin fa-3x"></i><br><span style="margin-top:12px;display:block;font-size:14px;">Loading document...</span>
                </div>
                <iframe id="docViewerFrame" src="" style="width:100%;height:78vh;border:none;display:none;" onload="docFrameLoaded()"></iframe>
                <div id="docViewerImg" style="display:none;text-align:center;padding:20px;background:#2d2d2d;">
                    <img id="docViewerImgEl" src="" style="max-width:100%;max-height:78vh;border-radius:6px;box-shadow:0 4px 20px rgba(0,0,0,0.5);" />
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="<?php echo base_url(); ?>backend/dist/js/hammer.min.js"></script>
<script type="text/javascript">

    function openDocViewer(url, filename) {
        var ext = filename ? filename.split('?')[0].split('.').pop().toLowerCase() : '';
        var imgExts = ['jpg','jpeg','png','gif','bmp','webp','svg'];
        var isImage = imgExts.indexOf(ext) !== -1;

        document.getElementById('docViewerDownload').href = url;
        document.getElementById('docViewerLoading').style.display = 'block';
        document.getElementById('docViewerFrame').style.display = 'none';
        document.getElementById('docViewerFrame').src = '';
        document.getElementById('docViewerImg').style.display = 'none';
        document.getElementById('docViewerImgEl').src = '';

        if (isImage) {
            var img = document.getElementById('docViewerImgEl');
            img.onload = img.onerror = function() {
                document.getElementById('docViewerLoading').style.display = 'none';
            };
            img.src = url;
            document.getElementById('docViewerImg').style.display = 'block';
        } else {
            document.getElementById('docViewerFrame').src = url;
            document.getElementById('docViewerFrame').style.display = 'block';
        }
        $('#docViewerModal').modal({ backdrop: true, keyboard: true });
    }

    function docFrameLoaded() {
        document.getElementById('docViewerLoading').style.display = 'none';
    }

    $('#docViewerModal').on('hidden.bs.modal', function () {
        document.getElementById('docViewerFrame').src = '';
        document.getElementById('docViewerImgEl').src = '';
    });

    (function() {

    const timeline = document.querySelector(".timeline-route ol"),
    elH = document.querySelectorAll(".timeline-route li > div"),
    arrows = document.querySelectorAll(".timeline-route .arrows .arrow"),
    arrowPrev = document.querySelector(".timeline-route .arrows .arrow__prev"),
    arrowNext = document.querySelector(".timeline-route .arrows .arrow__next"),
    firstItem = document.querySelector(".timeline-route li:first-child"),
    lastItem = document.querySelector(".timeline-route li:last-child"),
    xScrolling = 280,
    disabledClass = "disabled";

  window.addEventListener("load", init);

  function init() {
    setEqualHeights(elH);
    animateTl(xScrolling, arrows, timeline);
    setSwipeFn(timeline, arrowPrev, arrowNext);
    setKeyboardFn(arrowPrev, arrowNext);
  }

  // SET EQUAL HEIGHTS
  function setEqualHeights(el) {
    let counter = 0;
    for (let i = 0; i < el.length; i++) {
      const singleHeight = el[i].offsetHeight;

      if (counter < singleHeight) {
        counter = singleHeight;
      }
    }

    for (let i = 0; i < el.length; i++) {
      el[i].style.height = `${counter}px`;
    }
  }

  function isElementInViewport(el) {
    const rect = el.getBoundingClientRect();
    return (
      rect.top >= 0 &&
      rect.left >= 0 &&
      rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
      rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
  }

  function setBtnState(el, flag = true) {
    if (flag) {
      el.classList.add(disabledClass);
    } else {
      if (el.classList.contains(disabledClass)) {
        el.classList.remove(disabledClass);
      }
      el.disabled = false;
    }
  }

  function animateTl(scrolling, el, tl) {
    let counter = 0;
    for (let i = 0; i < el.length; i++) {
      el[i].addEventListener("click", function() {
        if (!arrowPrev.disabled) {
          arrowPrev.disabled = true;
        }
        if (!arrowNext.disabled) {
          arrowNext.disabled = true;
        }
        const sign = (this.classList.contains("arrow__prev")) ? "" : "-";
        if (counter === 0) {
          tl.style.transform = `translateX(-${scrolling}px)`;
        } else {
          const tlStyle = getComputedStyle(tl);
          const tlTransform = tlStyle.getPropertyValue("-webkit-transform") || tlStyle.getPropertyValue("transform");
          const values = parseInt(tlTransform.split(",")[4]) + parseInt(`${sign}${scrolling}`);
          tl.style.transform = `translateX(${values}px)`;
        }

        setTimeout(() => {
          isElementInViewport(firstItem) ? setBtnState(arrowPrev) : setBtnState(arrowPrev, false);
          isElementInViewport(lastItem) ? setBtnState(arrowNext) : setBtnState(arrowNext, false);
        }, 1100);

        counter++;
      });
    }
  }

  function setSwipeFn(tl, prev, next) {
    const hammer = new Hammer(tl);
    hammer.on("swipeleft", () => next.click());
    hammer.on("swiperight", () => prev.click());
  }

  function setKeyboardFn(prev, next) {
    document.addEventListener("keydown", (e) => {
      if ((e.which === 37) || (e.which === 39)) {
        const timelineOfTop = timeline.offsetTop;
        const y = window.pageYOffset;
        if (timelineOfTop !== y) {
          window.scrollTo(0, timelineOfTop);
        }
        if (e.which === 37) {
          prev.click();
        } else if (e.which === 39) {
          next.click();
        }
      }
    });
  }

})();
</script>