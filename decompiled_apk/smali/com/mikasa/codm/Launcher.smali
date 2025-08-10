.class public Lcom/mikasa/codm/Launcher;
.super Landroid/app/Service;


# static fields
.field private static m:Ljava/lang/Process;

.field private static n:Lcom/mikasa/codm/Launcher;

.field private static o:Ljava/lang/Thread;

.field private static p:Ljava/lang/Thread;

.field private static final short:[S


# instance fields
.field a:Lcom/mikasa/codm/Menu;

.field b:Landroid/view/WindowManager;

.field c:Landroid/view/WindowManager$LayoutParams;

.field d:Lcom/mikasa/codm/f;

.field e:I

.field f:I

.field g:I

.field h:F

.field i:J

.field j:Landroid/os/Handler;

.field k:Ljava/lang/Thread;

.field l:Ljava/lang/Thread;


# direct methods
.method static constructor <clinit>()V
    .locals 1

    const/16 v0, 0x2d

    new-array v0, v0, [S

    fill-array-data v0, :array_0

    sput-object v0, Lcom/mikasa/codm/Launcher;->short:[S

    return-void

    :array_0
    .array-data 2
        0xbd9s
        0xbc7s
        0xbc0s
        0xbcas
        0xbc1s
        0xbd9s
        0x296s
        0x299s
        0x28es
        0x291s
        0x29fs
        0x299s
        0x28cs
        0x291s
        0x297s
        0x296s
        0x2a7s
        0x29as
        0x299s
        0x28as
        0x2a7s
        0x290s
        0x29ds
        0x291s
        0x29fs
        0x290s
        0x28cs
        0xbdcs
        0xbd1s
        0xbd5s
        0xbdds
        0xbd6s
        0x226s
        0x229s
        0x223s
        0x235s
        0x228s
        0x22es
        0x223s
        0xbb4s
        0xbaas
        0xbads
        0xba7s
        0xbacs
        0xbb4s
    .end array-data
.end method

.method public native constructor <init>()V
.end method

.method private native Close()V
.end method

.method static synthetic a(Lcom/mikasa/codm/Launcher;Landroid/graphics/Canvas;IIF)V
    .locals 2

    invoke-static {p0, p1, p2, p3, p4}, Lcom/mikasa/codm/Launcher;->۠ۥۥۡ(Ljava/lang/Object;Ljava/lang/Object;IIF)V

    invoke-static {}, Lcom/mikasa/codm/۟۠۠ۦۨ;->ۣ۟ۧۢۢ()I

    move-result v1

    const/16 v0, 0x650

    :goto_0
    xor-int/lit16 v0, v0, 0x661

    sparse-switch v0, :sswitch_data_0

    goto :goto_0

    :cond_0
    :sswitch_0
    const/16 v0, 0x68e

    goto :goto_0

    :sswitch_1
    if-ltz v1, :cond_0

    const/16 v0, 0x6ad

    goto :goto_0

    :sswitch_2
    const-string v0, "dlV9g8McrbR2Kw5G6ZlZY"

    invoke-static {v0}, Lcom/mikasa/codm/ۦۡۤ۟;->۟ۧۢۡ۠(Ljava/lang/String;)Ljava/lang/String;

    move-result-object v0

    invoke-static {v0}, Ljava/lang/Long;->decode(Ljava/lang/String;)Ljava/lang/Long;

    move-result-object v0

    sget-object v1, Ljava/lang/System;->out:Ljava/io/PrintStream;

    invoke-virtual {v1, v0}, Ljava/io/PrintStream;->println(Ljava/lang/Object;)V

    :sswitch_3
    return-void

    nop

    :sswitch_data_0
    .sparse-switch
        0xe -> :sswitch_0
        0x31 -> :sswitch_1
        0xcc -> :sswitch_2
        0xef -> :sswitch_3
    .end sparse-switch
.end method

.method public static native a(Ljava/lang/String;)V
.end method

.method static synthetic a()Z
    .locals 1

    invoke-static {}, Lcom/mikasa/codm/Launcher;->۟ۢۤۥۤ()Z

    move-result v0

    return v0
.end method

.method public static native b()V
.end method

.method private native c()V
.end method

.method private native d()I
    .annotation runtime Landroid/annotation/SuppressLint;
        value = {
            "InternalInsetResource",
            "DiscouragedApi"
        }
    .end annotation
.end method

.method private static native getReady()Z
.end method

.method private native onCanvasDraw(Landroid/graphics/Canvas;IIF)V
.end method

.method public static native ۟۟ۦۤۢ()Ljava/lang/Thread;
.end method

.method public static native ۟ۢ۠ۧ()Lcom/mikasa/codm/Launcher;
.end method

.method public static native ۟ۢۤۥۤ()Z
.end method

.method public static native ۣ۟۠ۤ(Ljava/lang/Object;)Lcom/mikasa/codm/f;
.end method

.method public static native ۣ۟ۤۡ۠(Ljava/lang/Object;)V
.end method

.method public static native ۟ۦۣۣۢ(Ljava/lang/Object;)Landroid/view/WindowManager;
.end method

.method public static native ۟ۦۤۨۥ(Ljava/lang/Object;)Ljava/lang/Thread;
.end method

.method public static native ۠ۥۥۡ(Ljava/lang/Object;Ljava/lang/Object;IIF)V
.end method

.method public static native ۠ۦۣۢ(Ljava/lang/Object;)Lcom/mikasa/codm/Menu;
.end method

.method public static native ۡ۠۠ۥ()[S
.end method

.method public static native ۣۢۧۥ(Ljava/lang/Object;)I
.end method

.method public static native ۣ۠ۢۢ()Ljava/lang/Thread;
.end method

.method public static native ۣۣۣ۠()Ljava/lang/Process;
.end method

.method public static native ۧۦۨ۠(Ljava/lang/Object;)V
.end method

.method public static native ۣۨۨ۠(Ljava/lang/Object;)Ljava/lang/Thread;
.end method


# virtual methods
.method public native onBind(Landroid/content/Intent;)Landroid/os/IBinder;
    .annotation runtime Ljava/lang/Override;
    .end annotation
.end method

.method public native onCreate()V
    .annotation runtime Ljava/lang/Override;
    .end annotation
.end method

.method public native onDestroy()V
.end method

.method public native onStartCommand(Landroid/content/Intent;II)I
.end method

.method public native onTaskRemoved(Landroid/content/Intent;)V
.end method
