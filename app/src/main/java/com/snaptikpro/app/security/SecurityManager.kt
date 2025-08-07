package com.snaptikpro.app.security

import android.content.Context
import android.util.Log
import okhttp3.*
import okhttp3.logging.HttpLoggingInterceptor
import java.io.IOException
import java.security.KeyStore
import java.security.cert.Certificate
import java.security.cert.CertificateFactory
import java.security.cert.X509Certificate
import java.util.concurrent.TimeUnit
import javax.net.ssl.*

class SecurityManager {
    
    companion object {
        private const val TAG = "SecurityManager"
        
        // Known good certificate fingerprints for TikTok API
        private val VALID_CERTIFICATE_PINS = arrayOf(
            "sha256/AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=", // Placeholder
            "sha256/BBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBBB="  // Placeholder
        )
        
        // Known good hostnames
        private val VALID_HOSTNAMES = arrayOf(
            "www.tikwm.com",
            "tikwm.com",
            "api.tikwm.com"
        )
    }
    
    fun createSecureOkHttpClient(): OkHttpClient {
        return OkHttpClient.Builder()
            .addInterceptor(createSecurityInterceptor())
            .addInterceptor(createAntiDebugInterceptor())
            .addInterceptor(createCertificatePinningInterceptor())
            .sslSocketFactory(createSSLSocketFactory(), createTrustManager())
            .hostnameVerifier(createHostnameVerifier())
            .connectTimeout(30, TimeUnit.SECONDS)
            .readTimeout(30, TimeUnit.SECONDS)
            .writeTimeout(30, TimeUnit.SECONDS)
            .build()
    }
    
    private fun createSecurityInterceptor(): Interceptor {
        return Interceptor { chain ->
            val request = chain.request()
            
            // Add security headers
            val secureRequest = request.newBuilder()
                .addHeader("User-Agent", "Mozilla/5.0 (Linux; Android 11; SM-G991B) AppleWebKit/537.36")
                .addHeader("Accept", "application/json, text/plain, */*")
                .addHeader("Accept-Language", "en-US,en;q=0.9")
                .addHeader("Accept-Encoding", "gzip, deflate, br")
                .addHeader("Connection", "keep-alive")
                .addHeader("Sec-Fetch-Dest", "empty")
                .addHeader("Sec-Fetch-Mode", "cors")
                .addHeader("Sec-Fetch-Site", "same-origin")
                .addHeader("Cache-Control", "no-cache")
                .addHeader("Pragma", "no-cache")
                .build()
            
            chain.proceed(secureRequest)
        }
    }
    
    private fun createAntiDebugInterceptor(): Interceptor {
        return Interceptor { chain ->
            // Check if app is being debugged
            if (isDebuggerAttached()) {
                Log.w(TAG, "Debugger detected, blocking request")
                throw IOException("Security violation: Debugger detected")
            }
            
            // Check if app is running in emulator
            if (isEmulator()) {
                Log.w(TAG, "Emulator detected, blocking request")
                throw IOException("Security violation: Emulator detected")
            }
            
            chain.proceed(chain.request())
        }
    }
    
    private fun createCertificatePinningInterceptor(): Interceptor {
        return Interceptor { chain ->
            val request = chain.request()
            val url = request.url
            
            // Verify hostname
            if (!isValidHostname(url.host)) {
                Log.e(TAG, "Invalid hostname: ${url.host}")
                throw IOException("Security violation: Invalid hostname")
            }
            
            chain.proceed(request)
        }
    }
    
    private fun createSSLSocketFactory(): SSLSocketFactory {
        return try {
            val trustManagerFactory = TrustManagerFactory.getInstance(TrustManagerFactory.getDefaultAlgorithm())
            trustManagerFactory.init(null as KeyStore?)
            val trustManagers = trustManagerFactory.trustManagers
            
            val sslContext = SSLContext.getInstance("TLS")
            sslContext.init(null, trustManagers, null)
            
            sslContext.socketFactory
        } catch (e: Exception) {
            Log.e(TAG, "Error creating SSL socket factory: ${e.message}")
            throw RuntimeException(e)
        }
    }
    
    private fun createTrustManager(): X509TrustManager {
        return object : X509TrustManager {
            override fun checkClientTrusted(chain: Array<X509Certificate>, authType: String) {
                // Always trust client certificates
            }
            
            override fun checkServerTrusted(chain: Array<X509Certificate>, authType: String) {
                // Verify server certificate
                if (chain.isEmpty()) {
                    throw IllegalArgumentException("Empty certificate chain")
                }
                
                val serverCert = chain[0]
                val fingerprint = getCertificateFingerprint(serverCert)
                
                if (!isValidCertificateFingerprint(fingerprint)) {
                    Log.e(TAG, "Invalid certificate fingerprint: $fingerprint")
                    throw IllegalArgumentException("Invalid certificate")
                }
            }
            
            override fun getAcceptedIssuers(): Array<X509Certificate> {
                return arrayOf()
            }
        }
    }
    
    private fun createHostnameVerifier(): HostnameVerifier {
        return HostnameVerifier { hostname, session ->
            if (!isValidHostname(hostname)) {
                Log.e(TAG, "Invalid hostname: $hostname")
                return@HostnameVerifier false
            }
            
            // Additional hostname verification
            val peerCert = session.peerCertificates.firstOrNull() as? X509Certificate
            if (peerCert != null) {
                val certHostname = peerCert.subjectAlternativeNames
                    ?.filter { it[0] == 2 } // DNS type
                    ?.map { it[1].toString() }
                    ?.firstOrNull()
                
                if (certHostname != null && !hostname.equals(certHostname, ignoreCase = true)) {
                    Log.e(TAG, "Hostname mismatch: $hostname vs $certHostname")
                    return@HostnameVerifier false
                }
            }
            
            true
        }
    }
    
    private fun isValidHostname(hostname: String): Boolean {
        return VALID_HOSTNAMES.any { validHostname ->
            hostname.equals(validHostname, ignoreCase = true) ||
            hostname.endsWith(".$validHostname", ignoreCase = true)
        }
    }
    
    private fun isValidCertificateFingerprint(fingerprint: String): Boolean {
        return VALID_CERTIFICATE_PINS.contains(fingerprint)
    }
    
    private fun getCertificateFingerprint(certificate: X509Certificate): String {
        val digest = java.security.MessageDigest.getInstance("SHA-256")
        val encoded = certificate.encoded
        val hash = digest.digest(encoded)
        return "sha256/" + android.util.Base64.encodeToString(hash, android.util.Base64.NO_WRAP)
    }
    
    private fun isDebuggerAttached(): Boolean {
        return android.os.Debug.isDebuggerConnected()
    }
    
    private fun isEmulator(): Boolean {
        return android.os.Build.FINGERPRINT.startsWith("generic") ||
               android.os.Build.FINGERPRINT.startsWith("unknown") ||
               android.os.Build.MODEL.contains("google_sdk") ||
               android.os.Build.MODEL.contains("Emulator") ||
               android.os.Build.MODEL.contains("Android SDK built for x86") ||
               android.os.Build.MANUFACTURER.contains("Genymotion") ||
               (android.os.Build.BRAND.startsWith("generic") && android.os.Build.DEVICE.startsWith("generic")) ||
               "google_sdk" == android.os.Build.PRODUCT
    }
}