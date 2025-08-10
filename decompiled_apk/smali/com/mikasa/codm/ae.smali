.class Lcom/mikasa/codm/ae;
.super Landroid/os/Handler;


# static fields
.field private static final short:[S


# instance fields
.field private final a:Lcom/mikasa/codm/ad;

.field private final b:Landroid/app/ProgressDialog;


# direct methods
.method static constructor <clinit>()V
    .locals 1

    const/16 v0, 0x14

    new-array v0, v0, [S

    fill-array-data v0, :array_0

    sput-object v0, Lcom/mikasa/codm/ae;->short:[S

    return-void

    :array_0
    .array-data 2
        0x7c2s
        0x7ces
        0x7ccs
        0x78fs
        0x7d5s
        0x7c4s
        0x7cfs
        0x7c2s
        0x7c4s
        0x7cfs
        0x7d5s
        0x78fs
        0x7d5s
        0x7ccs
        0x7c6s
        0x7d1s
        0x78fs
        0x7c2s
        0x7ces
        0x7c5s
    .end array-data
.end method

.method native constructor <init>(Lcom/mikasa/codm/ad;Landroid/app/ProgressDialog;)V
.end method

.method public static native ۟ۥ۠۟ۢ(Ljava/lang/Object;)Lcom/mikasa/codm/MainActivity;
.end method

.method public static native ۟ۥۧۧۧ(Ljava/lang/Object;)Lcom/mikasa/codm/ad;
.end method

.method public static native ۣۤۨ۠(Ljava/lang/Object;)Landroid/app/ProgressDialog;
.end method

.method public static native ۤۦۦ()[S
.end method

.method public static native ۥۦۣۤ(Ljava/lang/Object;)V
.end method


# virtual methods
.method public native handleMessage(Landroid/os/Message;)V
    .annotation runtime Ljava/lang/Override;
    .end annotation
.end method
