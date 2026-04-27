<?php
/**
 * @var $this    View
 * @var $editors Editor[]
 */
?>

<div class="page-body">
    <div class="container-xl">
        <div class="row row-cards">
            <div class="col-md-6">
                <div class="card card-md">
                    <div class="card-body">
                        <h3 class="h2 color-primary" style="line-height: 1.2em">
                            <svg version="1.1" id="nouveauprojet-ship-bg" xmlns="http://www.w3.org/2000/svg"
                                 xmlns:xlink="http://www.w3.org/1999/xlink" x="0px"
                                 y="0px" viewBox="0 0 45.9 47.9" style="opacity: 0.2;max-width: 1.1em; enable-background:new 0 0 45.9 47.9;"
                                 xml:space="preserve">
                                    <style type="text/css">
                                        .st0 {
                                            fill: #E20E18;
                                        }

                                        .st1 {
                                            fill: #FFFFFF;
                                        }
                                    </style>
                                <g>
                                    <g>
                                        <path class="st0"
                                              d="M32.3,30.3l1.3,7.2c0,0-1.7,3.1-4.6,5.8c-2.9,2.7-8.2,4.5-8.2,4.5l-2.1-9.1c0,0,5.3-2.8,6.9-4 S32.3,30.3,32.3,30.3z"/>
                                        <path class="st0"
                                              d="M44.9,0.4c0,0-10.7-2.4-18.5,4.2S10.1,30.4,10.1,30.4l4.1,3.8l4.1,3.8c0,0,18.3-10.2,24.1-18.7C48.4,10.8,44.9,0.4,44.9,0.4z"/>
                                        <g>
                                            <path class="st1"
                                                  d="M19.3,20.8c-0.7-0.7-0.6-1.9,0.2-2.7L32,5.2c0.8-0.8,2-0.9,2.7-0.3c0.4,0.4,0.5,1,0.4,1.6l-2.3,10.9c-0.1,0.6-0.6,1-1.2,1.2l-10.8,2.6C20.3,21.4,19.7,21.2,19.3,20.8L19.3,20.8z"/>
                                            <path class="st1"
                                                  d="M41.6,15.5L30,27.4c-1.1,1.1-2.7,1.2-3.6,0.4L21,22.5l10.8-2.6c1.1-0.3,2.1-1.2,2.3-2.4l2.3-10.9l5.5,5.3C42.8,12.8,42.7,14.4,41.6,15.5L41.6,15.5z"/>
                                        </g>
                                        <path class="st0"
                                              d="M16.4,15.8l-7.3-0.6c0,0-2.9,2-5.4,5.1C1.3,23.4,0,28.9,0,28.9l9.3,1.2c0,0,2.3-5.5,3.3-7.3S16.4,15.8,16.4,15.8L16.4,15.8z"/>


                                    </g>
                                    <path id="flame" class="st0"
                                          d="M14.5,37l-2.8,0l-0.2-2.8c0,0-4.8-0.8-7.8,3.4c-2.8,3.8-1.6,8.9-1.4,9.7c0,0,0,0.1,0,0.1s0,0,0.1,0c0,0,0,0.1,0,0.1s0.1-0.1,0.1-0.1c0.8,0.1,6,0.8,9.5-2.3C15.7,41.6,14.5,37,14.5,37z">
                                        <animateTransform
                                                attributeName="transform"
                                                attributeType="XML"
                                                type="translate"
                                                begin="0s"
                                                dur="0.5s"
                                                repeatCount="indefinite"
                                                values="0 0;-0.5 0.5;0 0"
                                                keyTimes="0;0.5;1"
                                                calcMode="spline"
                                                keySplines="0.42 0 0.58 1;0.42 0 0.58 1"
                                        />
                                        <animate
                                                attributeName="opacity"
                                                attributeType="XML"
                                                from="1"
                                                to="0.5"
                                                begin="0s"
                                                dur="0.5s"
                                                repeatCount="indefinite"
                                                values="1;0.5;1"
                                                keyTimes="0;0.5;1"
                                                calcMode="spline"
                                                keySplines="0.42 0 0.58 1;0.42 0 0.58 1"
                                        />
                                    </path>
                                    <animateTransform
                                            attributeName="transform"
                                            attributeType="XML"
                                            type="translate"
                                            begin="0s"
                                            dur="2s"
                                            repeatCount="indefinite"
                                            values="0 0;-0.5 0.5;0 0"
                                            keyTimes="0;0.5;1"
                                            calcMode="spline"
                                            keySplines="0.42 0 0.58 1;0.42 0 0.58 1"
                                    />
                                </g>
                                </svg>
                            <?=\NGine\Translate::get("nouveauprojet.home.heading");?>
                        </h3>
                        <div class="text-secondary text-justify">
                            <!-- <?= \NGine\Translate::get("nouveauprojet.home.welcome1"); ?> -->
                             LifeFor
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card card-md h-100">
                    <div class="card-body">
                        <div class="text-secondary text-justify">
                            <?= \NGine\Translate::get("nouveauprojet.home.welcome2"); ?>
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-md-12">
                <div class="card card-md">
                    <div class="card-body">
                        <div class="text-secondary text-center">
                            <img class="mb-4" src="/assets/img/nouveauprojet-Visuel-couverture-LinkedIn-768x192.png" alt="nouveauprojet visuel couverture LinkedIn">
                            <?= \NGine\Translate::get("nouveauprojet.home.download"); ?>
                            <div class="row">
                                <div class="col-md-6 mt-4 text-lg-right">
                                    <a href="https://apps.apple.com/fr/app/nouveauprojet/id6474763944"><img src="/assets/img/store-apple.png" alt="Get nouveauprojet App from Apple store" width="160" height="45" style="height: 50px; width: auto;"></a>
                                </div>
                                <div class="col-md-6 mt-4 text-lg-left">
                                    <a href="https://play.google.com/store/apps/details?id=com.digitalfit.nouveauprojet"><img src="/assets/img/store-android.png" alt="Get nouveauprojet App from Google Play store" width="160" height="45" style="height: 50px; width: auto;"></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>