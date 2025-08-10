package app.witwork.boosterlike.common.extension

import android.widget.FrameLayout
import androidx.fragment.app.Fragment
import app.witwork.boosterlike.R
import app.witwork.boosterlike.domain.model.Config
import com.google.android.gms.ads.AdListener
import com.google.android.gms.ads.AdSize
import com.google.android.gms.ads.AdView
import timber.log.Timber

fun Fragment.setUpAd(config: Config) {
    val adViewContainer = view?.findViewById<FrameLayout>(R.id.adViewContainer)
    val bannerId = config.data?.google?.findLast { it.adType == "bottom-ads" }?.adId ?: ""

    val adView = AdView(requireContext())
        .apply {
            setAdSize(AdSize.FULL_BANNER)
            adUnitId = bannerId
            setup()
            adListener = object : AdListener() {
                override fun onAdLoaded() {
                    super.onAdLoaded()
                    Timber.i("onAdLoaded")
                }
            }
        }
    adViewContainer?.addView(adView)
}