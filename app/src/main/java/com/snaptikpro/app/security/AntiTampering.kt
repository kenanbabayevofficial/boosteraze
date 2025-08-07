package com.snaptikpro.app.security

import android.content.Context
import android.content.pm.PackageManager
import android.os.Build
import android.util.Log
import java.io.File
import java.io.IOException

class AntiTampering(private val context: Context) {
    
    companion object {
        private const val TAG = "AntiTampering"
    }
    
    fun performSecurityChecks(): Boolean {
        try {
            // Check for root
            if (isDeviceRooted()) {
                Log.w(TAG, "Root detected")
                return false
            }
            
            // Check for app tampering
            if (isAppTampered()) {
                Log.w(TAG, "App tampering detected")
                return false
            }
            
            // Check for debugger
            if (isDebuggerAttached()) {
                Log.w(TAG, "Debugger detected")
                return false
            }
            
            // Check for emulator
            if (isEmulator()) {
                Log.w(TAG, "Emulator detected")
                return false
            }
            
            // Check for suspicious apps
            if (hasSuspiciousApps()) {
                Log.w(TAG, "Suspicious apps detected")
                return false
            }
            
            return true
        } catch (e: Exception) {
            Log.e(TAG, "Error during security checks: ${e.message}")
            return false
        }
    }
    
    private fun isDeviceRooted(): Boolean {
        val buildTags = Build.TAGS
        if (buildTags != null && buildTags.contains("test-keys")) {
            return true
        }
        
        // Check for common root apps
        val rootApps = arrayOf(
            "com.noshufou.android.su",
            "com.thirdparty.superuser",
            "eu.chainfire.supersu",
            "com.topjohnwu.magisk",
            "com.kingroot.kinguser",
            "com.kingo.root",
            "com.smedialink.oneclickroot",
            "com.qihoo.permmgr",
            "com.alephzain.framaroot"
        )
        
        val packageManager = context.packageManager
        for (rootApp in rootApps) {
            try {
                packageManager.getPackageInfo(rootApp, 0)
                return true
            } catch (e: PackageManager.NameNotFoundException) {
                // App not found, continue checking
            }
        }
        
        // Check for common root binaries
        val rootBinaries = arrayOf(
            "/system/app/Superuser.apk",
            "/sbin/su",
            "/system/bin/su",
            "/system/xbin/su",
            "/data/local/xbin/su",
            "/data/local/bin/su",
            "/system/sd/xbin/su",
            "/system/bin/failsafe/su",
            "/data/local/su",
            "/su/bin/su"
        )
        
        for (binary in rootBinaries) {
            if (File(binary).exists()) {
                return true
            }
        }
        
        // Check for writable system partition
        try {
            val systemDir = File("/system")
            if (systemDir.canWrite()) {
                return true
            }
        } catch (e: Exception) {
            // Ignore exceptions
        }
        
        return false
    }
    
    private fun isAppTampered(): Boolean {
        try {
            val packageInfo = context.packageManager.getPackageInfo(context.packageName, PackageManager.GET_SIGNATURES)
            val signatures = packageInfo.signatures
            
            // Check if app is signed with debug key
            for (signature in signatures) {
                val signatureString = signature.toCharsString()
                if (signatureString.contains("Android Debug")) {
                    return true
                }
            }
            
            // Check for APK modifications
            val sourceDir = context.applicationInfo.sourceDir
            val apkFile = File(sourceDir)
            
            if (!apkFile.exists()) {
                return true
            }
            
            // Check file permissions
            if (apkFile.canWrite()) {
                return true
            }
            
        } catch (e: Exception) {
            Log.e(TAG, "Error checking app tampering: ${e.message}")
            return true
        }
        
        return false
    }
    
    private fun isDebuggerAttached(): Boolean {
        return android.os.Debug.isDebuggerConnected()
    }
    
    private fun isEmulator(): Boolean {
        return Build.FINGERPRINT.startsWith("generic") ||
               Build.FINGERPRINT.startsWith("unknown") ||
               Build.MODEL.contains("google_sdk") ||
               Build.MODEL.contains("Emulator") ||
               Build.MODEL.contains("Android SDK built for x86") ||
               Build.MANUFACTURER.contains("Genymotion") ||
               (Build.BRAND.startsWith("generic") && Build.DEVICE.startsWith("generic")) ||
               "google_sdk" == Build.PRODUCT ||
               Build.HARDWARE.contains("goldfish") ||
               Build.HARDWARE.contains("ranchu") ||
               Build.HARDWARE.contains("vbox86")
    }
    
    private fun hasSuspiciousApps(): Boolean {
        val suspiciousApps = arrayOf(
            "com.httptoolkit",
            "com.charles",
            "com.proxyman",
            "com.burp",
            "com.wireshark",
            "com.packetcapture",
            "com.networkminer",
            "com.httpcanary",
            "com.packetanalyzer",
            "com.networkmonitor"
        )
        
        val packageManager = context.packageManager
        for (app in suspiciousApps) {
            try {
                packageManager.getPackageInfo(app, 0)
                return true
            } catch (e: PackageManager.NameNotFoundException) {
                // App not found, continue checking
            }
        }
        
        return false
    }
    
    fun throwSecurityException(message: String) {
        throw SecurityException("Security violation: $message")
    }
}