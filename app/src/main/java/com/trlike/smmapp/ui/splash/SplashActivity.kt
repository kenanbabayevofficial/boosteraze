package com.trlike.smmapp.ui.splash

import android.content.Intent
import android.os.Bundle
import android.os.Handler
import android.os.Looper
import androidx.appcompat.app.AppCompatActivity
import com.trlike.smmapp.R
import com.trlike.smmapp.data.SessionManager
import com.trlike.smmapp.ui.login.LoginActivity
import com.trlike.smmapp.ui.main.MainActivity

class SplashActivity : AppCompatActivity() {
    
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContentView(R.layout.activity_splash)
        
        // Initialize SessionManager
        SessionManager.init(this)
        
        // Check if user is logged in
        Handler(Looper.getMainLooper()).postDelayed({
            val intent = if (SessionManager.isLoggedIn()) {
                Intent(this, MainActivity::class.java)
            } else {
                Intent(this, LoginActivity::class.java)
            }
            startActivity(intent)
            finish()
        }, 2000) // 2 seconds delay
    }
}