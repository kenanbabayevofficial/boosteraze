package app.witwork.boosterlike.presentation.profile

import android.content.Intent
import androidx.core.content.ContextCompat
import androidx.lifecycle.Observer
import androidx.lifecycle.ViewModelProvider
import app.witwork.boosterlike.R
import app.witwork.boosterlike.common.base.BaseFragment
import app.witwork.boosterlike.common.base.BasePresenter
import app.witwork.boosterlike.common.di.component.AppComponent
import app.witwork.boosterlike.common.dialog.WitConfirmDialogFragment
import app.witwork.boosterlike.common.extension.*
import app.witwork.boosterlike.common.utils.SharePref
import app.witwork.boosterlike.common.widget.CurveDrawable
import app.witwork.boosterlike.domain.model.Data
import app.witwork.boosterlike.presentation.AppViewModel
import app.witwork.boosterlike.presentation.MainActivity
import app.witwork.boosterlike.presentation.getMainFragment
import app.witwork.boosterlike.presentation.login.LoginActivity
import com.google.android.gms.ads.AdRequest
import com.google.android.gms.ads.FullScreenContentCallback
import com.google.android.gms.ads.MobileAds
import com.google.android.gms.ads.rewarded.RewardedAd
import com.google.android.gms.ads.rewarded.RewardedAdLoadCallback
import kotlinx.android.synthetic.main.fragment_profile.*
import timber.log.Timber
import javax.inject.Inject


class ProfileFragment : BaseFragment<ProfileView, ProfilePresenter>(), ProfileView {
    @Inject
    lateinit var presenter: ProfilePresenter

    private lateinit var appViewModel: AppViewModel

    override fun inject(appComponent: AppComponent) {
        appComponent.inject(this)
    }

    override fun presenter(): BasePresenter<ProfileView>? {
        return presenter
    }

    override fun viewIF(): ProfileView? {
        return this
    }

    override fun getLayoutRes(): Int {
        return R.layout.fragment_profile
    }

    override fun initView() {
        val useContext = context ?: return
        val curveDrawable = CurveDrawable(
            context.dp2Px(300f),
            ContextCompat.getColor(useContext, R.color.colorBackground)
        )
        container.background = curveDrawable

        idBtnLogout.bringToFront()

        initAction()
        MainActivity.appConfig.observe(viewLifecycleOwner, Observer {
            if (it != null) {
                setUpAd(it)
            }
        })
    }

    private fun showProfileInfo(profile: Data?) {
        profile?.apply {
            idProfileAvatar.loadImage(profile.covers)
            idProfileName.text = "@${profile.uniqueId}"
            idProfileFollowers.text = "${profile.following?.withSuffix()}"
            idProfileFollowing.text = "${profile.fans?.withSuffix()}"
            idProfileLikes.text = "${profile.heart?.withSuffix()}"
            idProfileMyStars.text = "${profile.stars} ⭐"
        }
    }

    private fun initAction() {
        idWatchAds.setOnClickListener {
            run {
                MainActivity.appConfig.value?.let { config ->
                    config.data?.google?.findLast { it.adType == "video-ads" }
                        ?.adId?.let { adId ->
                            setupRewardedAd(adId)
                        }
                }
            }
        }

        idBtnLogout.setOnClickListener {
            run {
                context?.setLocalStore("APP", "user", "")
                val intent = Intent(requireContext(), LoginActivity::class.java)
                startActivity(intent)
                activity?.finish()
            }
        }

        idGetStars.setOnClickListener {
            run {
                (activity as MainActivity).getMainFragment().setCurrentTab(2)
            }
        }

        idFollowTiktok.setOnClickListener {
            run {
                val profile = (activity as MainActivity).getMainFragment().profile()
                putStringPref(SharePref.KEY_USER_ID, profile?.id!!)
                context?.openLink("cunqbu")
            }
        }
    }

    override fun initData() {
        super.initData()

        appViewModel = ViewModelProvider(requireActivity())[AppViewModel::class.java]

        appViewModel.profile.observe(viewLifecycleOwner, Observer {
            if (it != null) {
                showProfileInfo(it.data)
            }
        })
    }


    /**
     * SETUP REWARDED AD
     */
    private fun setupRewardedAd(adId: String) {
        val fullScreenContentCallback = object : FullScreenContentCallback() {
            override fun onAdShowedFullScreenContent() {
                Timber.d("onAdShowedFullScreenContent #called()")
            }

            override fun onAdDismissedFullScreenContent() {
                val profile = (activity as MainActivity).getMainFragment().profile()
                presenter.rewardVideo(profile?.id!!)
            }
        }
        RewardedAd.load(
            requireContext(), adId,
            AdRequest.Builder().build(),
            object : RewardedAdLoadCallback() {
                override fun onAdLoaded(ad: RewardedAd) {
                    ad.fullScreenContentCallback = fullScreenContentCallback
                    ad.show(
                        requireActivity()
                    ) { }
                }
            }
        )
    }

    override fun onRewardVideoSuccess() {
        showAlert()
    }

    override fun onFollowSuccess() {
        showAlert(3)
    }

    private fun showAlert(stars: Int = 2) {
        (activity as MainActivity).getMainFragment().getProfile()
        WitConfirmDialogFragment.open(
            fragmentManager = childFragmentManager,
            type = WitConfirmDialogFragment.REWARD_START,
            stars = "+0$stars ⭐",
            onPositiveClicked = {
            }
        )
    }
}
